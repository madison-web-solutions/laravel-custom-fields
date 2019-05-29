<?php
namespace MadisonSolutions\LCF;

use Illuminate\Database\Eloquent\Model;

class ModelField extends Field
{
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
        return 'search-input';
    }

    protected function newInstance() : Model
    {
        return new $this->model_class;
    }

    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = function ($attribute, $value, $fail) {
            $dummy = $this->newInstance();
            $query = $dummy->where($dummy->getKeyName(), $value);
            if ($this->criteria) {
                $query->where($this->criteria);
            }
            if (! $query->exists()) {
                $fail("Invalid reference for {$attribute}");
            }
        };
        return $rules;
    }

    protected function testTypeNotNull($input) : bool
    {
        return ($input instanceof $this->model_class);
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        if ($input instanceof $this->model_class) {
            $output = $input;
            return true;
        }
        $obj = $this->newInstance()->find($input);
        if ($obj) {
            $output = $obj;
            return true;
        }
        $output = null;
        return false;
    }

    protected function toPrimitiveNotNull($cast_value)
    {
        return $cast_value->getKey();
    }

    public function getSuggestions(string $search)
    {
        $dummy = $this->newInstance();
        $query = $dummy->query();
        if ($this->criteria) {
            $query->where($this->criteria);
        }
        $query->where(function ($q) use ($search) {
            // @todo scout?
            $search_esc = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search) . '%';
            foreach (array_values($this->search_fields) as $i => $search_field) {
                if ($i == 0) {
                    $q->where($search_field, 'ilike', $search_esc); // @todo ilike is postgres extension - so this makes LCF postgres only!
                } else {
                    $q->orWhere($search_field, 'ilike', $search_esc);
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
