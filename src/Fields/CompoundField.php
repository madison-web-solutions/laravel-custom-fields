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
            $messages[$path][] = "Invalid value";
            return;
        }
        foreach ($this->sub_fields as $key => $field) {
            if ($validator && $this->testCondition($key, $validator->getData(), $path) === false) {
                continue;
            }
            $field->validate("{$path}.{$key}", $value[$key] ?? null, $messages, $validator);
        }
    }

    // Given a relative path in 'dot notation', which is relative to the supplied absolute base path,
    // Calculate the resulting absolute path.
    // Caret characters (^) at the start of the relative path mean 'go up one level'
    protected function resolveRelativePath(string $basePath, string $relativePath)
    {
        $basePath = explode('.', $basePath);
        while ($relativePath[0] === '^') {
            array_pop($basePath);
            $relativePath = substr($relativePath, 1);
        }
        return implode($basePath, '.') . '.' . $relativePath;
    }

    protected function testCondition(string $key, array $data, string $path)
    {
        $conditionDefn = $this->conditions ? ($this->conditions[$key] ?? null) : null;
        if (! $conditionDefn) {
            return true;
        }

        $conditionType = $conditionDefn[0];
        $otherFieldPath = $this->resolveRelativePath($path, $conditionDefn[1]);
        $otherFieldValue = data_get($data, $otherFieldPath);
        $conditionValue = $conditionDefn[2];

        switch ($conditionType) {
            case 'eq':
                return $otherFieldValue === $conditionValue;
            case 'in':
                return in_array($otherFieldValue, $conditionValue);
        }
        return true;
    }

    public function getSubField(string $key)
    {
        return $this->sub_fields[$key] ?? null;
    }

    protected function doWalk(callable $callback, $cast_value, array $path, ...$params)
    {
        $callback($this, $cast_value, $path, ...$params);
        if (is_null($cast_value)) {
            return;
        }
        foreach ($this->sub_fields as $key => $field) {
            array_push($path, $key);
            $field->doWalk($callback, $cast_value[$key] ?? null, $path, ...$params);
            array_pop($path);
        }
    }
}
