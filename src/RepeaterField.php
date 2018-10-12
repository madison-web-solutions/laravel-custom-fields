<?php
namespace MadisonSolutions\LCF;

use Illuminate\Support\MessageBag;

class RepeaterField extends Field
{
    protected $store_in_json = true;

    public function optionDefaults() : array
    {
        return parent::optionDefaults();
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['sub_field'] = [Field::isFieldRule()];
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'lcf-repeater-input';
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
        $ok = true;
        if (! is_array($input)) {
            $input = [$input];
        }
        if (count($input) == 0) {
            $output = null;
            return true;
        }
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
}
