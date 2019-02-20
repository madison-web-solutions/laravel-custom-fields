<?php
namespace MadisonSolutions\LCF;

use Illuminate\Support\MessageBag;

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

    public function inputComponent() : string
    {
        return 'compound-input';
    }


    protected function testTypeNotNull($input) : bool
    {
        if (! is_array($input)) {
            return false;
        }
        foreach ($this->sub_fields as $key => $field) {
            $value = $input[$key] ?? null;
            if (! $field->testType($value)) {
                return false;
            }
        }
        return true;
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        $ok = true;
        if (! is_array($input)) {
            return false;
        }
        $output = [];
        foreach ($this->sub_fields as $key => $field) {
            $sub_input = $input[$key] ?? null;
            if (! $field->doCoerce($sub_input, $sub_output, $on_fail)) {
                $ok = false;
            }
            $output[$key] = $sub_output;
        }
        return $ok;
    }

    protected function toPrimitiveNotNull($cast_value)
    {
        $primitive_value = [];
        foreach ($this->sub_fields as $key => $field) {
            $primitive_value[$key] = $field->toPrimitive($cast_value[$key]);
        }
        return $primitive_value;
    }

    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = 'array';
        return $rules;
    }

    public function validate(array $data, string $path, MessageBag $messages)
    {
        parent::validate($data, $path, $messages);
        $my_data = data_get($data, $path);
        if (is_array($my_data)) {
            foreach ($this->sub_fields as $key => $field) {
                if ($this->testCondition($key, $data, $path) !== false) {
                    $field->validate($data, "{$path}.{$key}", $messages);
                }
            }
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
