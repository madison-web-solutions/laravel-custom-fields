<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\LCF\Field;
use MadisonSolutions\LCF\Validator;

class CompoundField extends Field
{
    protected $store_in_json = true;

    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        unset($defaults['default']);
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['sub_fields'] = ['required', 'array', Field::simpleStringKeysRule(), 'min:1'];
        $rules['sub_fields.*'] = [Field::isFieldRule()];
        return $rules;
    }

    public function fieldComponent() : string
    {
        return 'lcf-compound-field';
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid) : bool
    {
        $ok = true;
        if (! is_array($input)) {
            return false;
        }
        $output = [];
        foreach ($this->sub_fields as $key => $field) {
            $sub_input = $input[$key] ?? null;
            if (! $field->coerce($sub_input, $sub_output, $keep_invalid)) {
                $ok = false;
            }
            $output[$key] = $sub_output;
        }
        return $ok;
    }

    public function validateNotNull(string $path, $value, &$messages, ?Validator $validator = null)
    {
        if (! is_array($value)) {
            $messages[$path][] = $this->trans('invalid');
            return;
        }
        foreach ($this->sub_fields as $key => $field) {
            $field->validate("{$path}.{$key}", $value[$key] ?? null, $messages, $validator);
        }
    }

    public function getSubField(string $key)
    {
        return $this->sub_fields[$key] ?? null;
    }

    protected function doWalk(callable $callback, $cast_value, array $path)
    {
        $callback($this, $cast_value, $path);
        if (is_null($cast_value)) {
            return;
        }
        foreach ($this->sub_fields as $key => $field) {
            array_push($path, $key);
            $field->doWalk($callback, $cast_value[$key] ?? null, $path);
            array_pop($path);
        }
    }

    protected function doMap(callable $callback, $cast_value, array $path)
    {
        if (is_null($cast_value)) {
            return null;
        }
        $mapped_value = [];
        foreach ($this->sub_fields as $key => $field) {
            array_push($path, $key);
            $mapped_value[$key] = $field->doMap($callback, $cast_value[$key] ?? null, $path);
            array_pop($path);
        }
        return $mapped_value;
    }
}
