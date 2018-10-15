<?php
namespace MadisonSolutions\LCF;

use Illuminate\Support\MessageBag;

class CompoundField extends Field
{
    protected $store_in_json = true;

    public function optionDefaults() : array
    {
        return parent::optionDefaults();
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
        return 'lcf-compound-input';
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
                $field->validate($data, "{$path}.{$key}", $messages);
            }
        }
    }

    public function getSubField(string $key)
    {
        return $this->sub_fields[$key] ?? null;
    }
}
