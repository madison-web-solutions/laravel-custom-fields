<?php
namespace MadisonSolutions\LCF;

use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use Validator;

class OptionsField extends Field
{
    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['choices'] = ['required', 'array', 'min:1'];
        $rules['choices.*'] = 'required|string';
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'options-input';
    }

    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = 'array';
        return $rules;
    }

    // Override the validation function to also validate the values in the submitted array
    public function validate(array $data, string $path, MessageBag $messages)
    {
        $rules = [];
        $rules[$path] = $this->getValidationRules();
        foreach ($this->choices as $value => $label) {
            $rules["{$path}.{$value}"] = 'in:true,false';
        }
        $validator = Validator::make($data, $rules);
        $messages->merge($validator->messages());
    }

    protected function testTypeNotNull($input) : bool
    {
        return is_array($input);
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        if (! is_array($input)) {
            return false;
        }
        $output = [];
        foreach ($this->choices as $value => $label) {
            if (! Coerce::toBool($input[$value] ?? false, $sub_output)) {
                return false;
            }
            $output[$value] = $sub_output;
        }
        return true;
    }
}
