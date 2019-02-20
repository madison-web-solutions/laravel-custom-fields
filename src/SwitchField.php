<?php
namespace MadisonSolutions\LCF;

use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use Validator;

class SwitchField extends Field
{
    protected $store_in_json = true;

    public function optionDefaults() : array
    {
        return parent::optionDefaults();
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['switch_fields'] = ['required', 'array', Field::simpleStringKeysRule('switch'), 'min:1'];
        $rules['switch_fields.*'] = [Field::isFieldRule()];
        $rules['switch_labels'] = 'nullable|array';
        $rules['switch_labels.*'] = 'required|string';
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'switch-input';
    }

    protected function testTypeNotNull($input) : bool
    {
        if (! ($input instanceof SwitchValue)) {
            return false;
        }
        if (!isset($this->switch_fields[$input->switch])) {
            return false;
        }
        $switch_field = $this->switch_fields[$input->switch];
        return $switch_field->testTypeNotNull($input->value);
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        if ($input instanceof SwitchValue) {
            if (! isset($this->switch_fields[$input->switch])) {
                return false;
            }
            $switch_name = $input->switch;
            $switch_value_input = $input->value;
        } elseif (is_array($input) && isset($input['switch']) && is_string($input['switch'])) {
            if (!isset($this->switch_fields[$input['switch']])) {
                // The switch field doesn't exist
                return false;
            }
            $switch_name = $input['switch'];
            $switch_value_input = $input[$switch_name] ?? null;
        } else {
            return false;
        }
        $switch_field = $this->switch_fields[$switch_name];
        $ok = $switch_field->doCoerce($switch_value_input, $switch_value_output, $on_fail);
        $output = new SwitchValue($switch_name, $switch_value_output);
        return $ok;
    }

    protected function toPrimitiveNotNull($cast_value)
    {
        $switch_name = $cast_value->switch;
        $switch_field = $this->switch_fields[$switch_name];
        return [
            'switch' => $switch_name,
            $switch_name => $switch_field->toPrimitive($cast_value->value),
        ];
    }

    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = 'array';
        return $rules;
    }

    public function validate(array $data, string $path, MessageBag $messages)
    {
        $rules = [
            $path => $this->getValidationRules(),
            "{$path}.switch" => ["required_with:{$path}", Rule::in(array_keys($this->switch_fields))],
        ];
        $validator = Validator::make($data, $rules);
        $messages->merge($validator->messages());
        $my_data = data_get($data, $path);
        if (is_array($my_data)) {
            $switch = $my_data['switch'] ?? '';
            $switch_field = $this->switch_fields[$switch] ?? null;
            if ($switch_field) {
                $switch_field->validate($data, "{$path}.{$switch}", $messages);
            }
        }
    }

    public function getSubField(string $key)
    {
        return $this->switch_fields[$key] ?? null;
    }

    protected function doWalk(callable $callback, $cast_value, array $path, ...$params)
    {
        $callback($this, $cast_value, $path, ...$params);
        if (is_null($cast_value)) {
            return;
        }
        $switch_name = $cast_value->switch;
        $switch_field = $this->switch_fields[$switch_name];
        array_push($path, $switch_name);
        $switch_field->doWalk($callback, $cast_value->value, $path, ...$params);
    }
}
