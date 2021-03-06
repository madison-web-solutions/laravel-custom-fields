<?php
namespace MadisonSolutions\LCF;

use Illuminate\Support\MessageBag;

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

    public function inputComponent() : string
    {
        return 'repeater-input';
    }

    protected function testTypeNotNull($input) : bool
    {
        if (! is_array($input)) {
            return false;
        }
        foreach ($input as $value) {
            if (! $this->sub_field->testType($value)) {
                return false;
            }
        }
        return true;
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        if (! is_array($input)) {
            $input = [$input];
        }
        if (count($input) == 0) {
            $output = null;
            return true;
        }
        $ok = true;
        $output = [];
        foreach ($input as $sub_input) {
            if (! $this->sub_field->doCoerce($sub_input, $sub_output, $on_fail)) {
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

    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = 'array';
        $max = $this->options['max'];
        if (is_int($max)) {
            $rules[] = "max:{$max}";
        }
        $min = $this->options['min'];
        if (is_int($min)) {
            $rules[] = "min:{$min}";
        }
        return $rules;
    }

    public function validate(array $data, string $path, MessageBag $messages)
    {
        parent::validate($data, $path, $messages);
        $my_data = data_get($data, $path);
        if (is_array($my_data)) {
            foreach (array_keys($my_data) as $i) {
                $this->sub_field->validate($data, "{$path}.{$i}", $messages);
            }
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
