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
        return 'lcf-repeater-field';
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

    public function validateNotNull(string $path, $value, &$messages, ?Validator $validator = null)
    {
        if (! is_array($value)) {
            $messages[$path][] = $this->trans('invalid');
            return;
        }
        if (count($value) == 0) {
            if ($this->options['required']) {
                $messages[$path][] = $this->trans('required');
            }
            return;
        }
        $max = $this->options['max'];
        if (is_int($max) && count($value) > $max) {
            $messages[$path][] = $this->trans('max', ['max' => $max]);
        }
        $min = $this->options['min'];
        if (is_int($min) && count($value) < $min) {
            $messages[$path][] = $this->trans('min', ['min' => $min]);
        }
        foreach ($value as $i => $sub_value) {
            $this->sub_field->validate("{$path}.{$i}", $sub_value, $messages, $validator);
        }
    }

    public function getSubField(string $key)
    {
        return $this->sub_field;
    }

    protected function doWalk(callable $callback, $cast_value, array $path)
    {
        $callback($this, $cast_value, $path);
        if (is_null($cast_value)) {
            return;
        }
        foreach ($cast_value as $i => $sub_value) {
            array_push($path, $i);
            $this->sub_field->doWalk($callback, $sub_value, $path);
            array_pop($path);
        }
    }

    protected function doMap(callable $callback, $cast_value, array $path)
    {
        if (is_null($cast_value)) {
            return null;
        }
        $mapped_value = [];
        foreach ($cast_value as $i => $sub_value) {
            array_push($path, $i);
            $mapped_value[$i] = $this->sub_field->doMap($callback, $sub_value, $path);
            array_pop($path);
        }
        return $mapped_value;
    }
}