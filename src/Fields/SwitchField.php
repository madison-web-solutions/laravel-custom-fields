<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\LCF\Field;
use MadisonSolutions\LCF\SwitchValue;

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
        return 'lcf-switch-field';
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid) : bool
    {
        if ($input instanceof SwitchValue) {
            if (! isset($this->switch_fields[$input->switch])) {
                $output = ($keep_invalid ? $input : null);
                return false;
            }
            $switch_name = $input->switch;
            $switch_value_input = $input->value;
        } elseif (is_array($input) && isset($input['switch']) && is_string($input['switch'])) {
            if (!isset($this->switch_fields[$input['switch']])) {
                // The switch field doesn't exist
                $output = ($keep_invalid ? $input : null);
                return false;
            }
            $switch_name = $input['switch'];
            $switch_value_input = $input[$switch_name] ?? null;
        } else {
            $output = ($keep_invalid ? $input : null);
            return false;
        }
        $switch_field = $this->switch_fields[$switch_name];
        $ok = $switch_field->coerce($switch_value_input, $switch_value_output, $keep_invalid);
        $output = new SwitchValue($switch_name, $switch_value_output);
        return $ok;
    }

    public function validateNotNull(string $path, $value, &$messages, array $data)
    {
        if (! ($value instanceof SwitchValue)) {
            $messages[$path][] = $this->trans('invalid');
            return;
        }
        if (! isset($this->switch_fields[$value->switch])) {
            $messages[$path][] = $this->trans('invalid');
            return false;
        }
        $switch_field = $this->switch_fields[$value->switch];
        $switch_field->validate("{$path}.{$value->switch}", $value->value, $messages, $data);
    }

    public function getSubField(string $key)
    {
        return $this->switch_fields[$key] ?? null;
    }

    protected function doWalk(callable $callback, $cast_value, array $path)
    {
        $callback($this, $cast_value, $path);
        if (is_null($cast_value)) {
            return;
        }
        $switch_name = $cast_value->switch;
        $switch_field = $this->switch_fields[$switch_name];
        array_push($path, $switch_name);
        $switch_field->doWalk($callback, $cast_value->value, $path);
    }

    protected function doMap(callable $callback, $cast_value, array $path)
    {
        if (is_null($cast_value)) {
            return null;
        }
        $switch_name = $cast_value->switch;
        $switch_field = $this->switch_fields[$switch_name];
        array_push($path, $switch_name);

        $mapped_switch_value = $switch_field->doMap($callback, $cast_value->value, $path);
        $mapped_value = new SwitchValue($switch_name, $mapped_switch_value);
        return $callback($this, $mapped_value, $path);
    }

    protected function expandPrepareNotNull($cast_value)
    {
        $switch_field = $this->switch_fields[$cast_value->switch];
        $switch_field->expandPrepare($cast_value->value);
    }

    protected function doExpandNotNull($cast_value)
    {
        $switch_field = $this->switch_fields[$cast_value->switch];
        $new_value = $switch_field->doExpand($cast_value->value);
        return new SwitchValue($cast_value->switch, $new_value);
    }
}
