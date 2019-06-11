<?php
namespace MadisonSolutions\LCF\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
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

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        $data['settings']['search_type'] = 'model:' . $this->model_class;
        $data['settings']['search_settings'] = [
            'model_class' => Arr::pull($data['settings'], 'model_class'),
            'criteria' => Arr::pull($data['settings'], 'criteria', null),
            'search_fields' => Arr::pull($data['settings'], 'search_fields'),
            'label_attribute' => Arr::pull($data['settings'], 'label_attribute'),
        ];
        return $data;
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
            if (Coerce::toInt($input, $output, Coerce::REJECT_BOOL)) {
                return true;
            }
        }
        $output = ($keep_invalid ? $input : null);
        return false;
    }
}
