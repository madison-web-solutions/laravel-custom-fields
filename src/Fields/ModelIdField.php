<?php
namespace MadisonSolutions\LCF\Fields;

use Illuminate\Database\Eloquent\Model;
use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\ScalarField;
use MadisonSolutions\LCF\LCF;
use MadisonSolutions\LCF\Validator;

class ModelIdField extends ScalarField
{
    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->options['string_keys'] = ($this->newInstance()->getKeyType() == 'string');
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['model_class'] = ['required', 'string', function ($attribute, $value, $fail) {
            if (! is_a($value, Model::class, true)) {
                $fail("{$attribute} must be a subclass of " . Model::class);
            }
        }];
        $rules['criteria'] = 'nullable|array';
        $rules['search_fields'] = 'required|array|min:1';
        $rules['search_fields.*'] = 'required|string';
        $rules['label_attribute'] = 'required|string';
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'lcf-search-input';
    }

    protected function newInstance() : Model
    {
        return new $this->model_class;
    }

    public function validateNotNull(string $path, $value, &$messages, ?Validator $validator = null)
    {
        if (! ($this->string_keys ? is_string($value) : is_int($value))) {
            $messages[$path][] = "Invalid value";
            return;
        }
        $dummy = $this->newInstance();
        $query = $dummy->where($dummy->getKeyName(), $value);
        if ($this->criteria) {
            $query->where($this->criteria);
        }
        if (! $query->exists()) {
            $messages[$path][] = "Model not found " . ($this->criteria ? 'matching the criteria' : '') . " with id {$value}";
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        if ($input instanceof $this->model_class) {
            $output = $input->getKey();
            return true;
        }
        if ($this->string_keys) {
            if (is_string($input) || is_int($input)) {
                $output = (string) $input;
                return true;
            }
        } else {
            if (is_numeric($input) && Coerce::toInt($input, $output)) {
                return true;
            }
        }
        $output = ($keep_invalid ? $input : null);
        return false;
    }

    public function getSuggestions(string $search)
    {
        $dummy = $this->newInstance();
        $query = $dummy->query();
        $ilike = LCF::iLikeOperator($query->getConnection());

        if ($this->criteria) {
            $query->where($this->criteria);
        }
        $query->where(function ($q) use ($ilike, $search) {
            // @todo scout?
            $search_esc = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search) . '%';
            foreach (array_values($this->search_fields) as $i => $search_field) {
                if ($i == 0) {
                    $q->where($search_field, $ilike, $search_esc);
                } else {
                    $q->orWhere($search_field, $ilike, $search_esc);
                }
            }
        });
        $suggestions = [];
        foreach ($query->take(10)->get() as $model) {
            $suggestions[] = [
                'id' => $model->getKey(),
                'label' => $model->getAttribute($this->label_attribute),
            ];
        }
        return $suggestions;
    }

    public function getDisplayName($id)
    {
        $dummy = $this->newInstance();
        $query = $dummy->query();
        if ($this->criteria) {
            $query->where($this->criteria);
        }
        $query->where($dummy->getKeyName(), $id);
        $model = $query->first();
        return $model ? $model->getAttribute($this->label_attribute) : '';
    }
}
