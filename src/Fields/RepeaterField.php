<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\LCF\Field;
use MadisonSolutions\LCF\Validator;

class RepeaterField extends Field
{
    protected $store_in_json = true;

    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        unset($defaults['default']);
        $defaults['max'] = null;
        $defaults['min'] = null;
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['sub_field'] = [Field::isFieldRule()];
        $rules['max'] = 'nullable|integer';
        $rules['min'] = 'nullable|integer';
        $rules['append_label'] = 'nullable|string';
        return $rules;
    }

    public function fieldComponent() : string
    {
        return 'repeater-field';
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        if (! is_array($input)) {
            $output = ($keep_invalid ? $input : null);
            return false;
        }
        if (count($input) == 0) {
            $output = null;
            return true;
        }
        $ok = true;
        $output = [];
        foreach ($input as $sub_input) {
            if (! $this->sub_field->coerce($sub_input, $sub_output, $keep_invalid)) {
                $ok = false;
            }
            $output[] = $sub_output;
        }
        return $ok;
    }

    protected function toPrimitiveNotNull($cast_value)
    {
        return array_map(function ($value) {
            return $this->sub_field->toPrimitive($value);
        }, $cast_value);
    }

    public function validateNotNull(string $path, $value, &$messages, Validator $validator)
    {
        if (! is_array($value)) {
            $messages[$path][] = "Invalid value";
            return;
        }
        $max = $this->options['max'];
        if (is_int($max) && count($value) > $max) {
            $messages[$path][] = "Maximum of {$max} entries";
        }
        $min = $this->options['min'];
        if (is_int($min) && count($value) < $min) {
            $messages[$path][] = "Minumum of {$min} entries";
        }
        foreach ($value as $i => $sub_value) {
            $this->sub_field->validate("{$path}.{$i}", $sub_value, $messages, $validator);
        }
    }

    public function getSubField(string $key)
    {
        return $this->sub_field;
    }

    protected function doWalk(callable $callback, $cast_value, array $path, ...$params)
    {
        $callback($this, $cast_value, $path, ...$params);
        if (is_null($cast_value)) {
            return;
        }
        foreach ($cast_value as $i => $sub_value) {
            array_push($path, $i);
            $this->sub_field->doWalk($callback, $sub_value, $path, ...$params);
            array_pop($path);
        }
    }
}
