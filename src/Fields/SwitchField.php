<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\LCF\Field;
use MadisonSolutions\LCF\SwtichValue;
use MadisonSolutions\LCF\Validator;

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

    public function fieldComponent() : string
    {
        return 'switch-field';
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        if ($input instanceof SwitchValue) {
            if (! isset($this->switch_fields[$input->switch])) {
                $output = null;
                return false;
            }
            $switch_name = $input->switch;
            $switch_value_input = $input->value;
        } elseif (is_array($input) && isset($input['switch']) && is_string($input['switch'])) {
            if (!isset($this->switch_fields[$input['switch']])) {
                // The switch field doesn't exist
                $output = null;
                return false;
            }
            $switch_name = $input['switch'];
            $switch_value_input = $input[$switch_name] ?? null;
        } else {
            $output = null;
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

    public function validateNotNull(string $path, $value, &$messages, Validator $validator)
    {
        if (! ($value instanceof SwitchValue)) {
            $messages[$path][] = "Invalid value";
            return;
        }
        if (! isset($this->switch_fields[$value->switch])) {
            $messages[$path][] = "Invalid value";
            return false;
        }
        $switch_field = $this->switch_fields[$value->switch];
        $switch_field->validate("{$path}.{$value->switch}", $value->value, $messages, $validator);
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
