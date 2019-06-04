<?php

namespace MadisonSolutions\LCF;

use Illuminate\Contracts\Validation\Rule;

class FieldValidationRule implements Rule
{
    protected $field;

    public function __construct(Field $field)
    {
        $this->field = $field;
    }

    public function passes($attribute, $value)
    {
        throw new \Exception("FieldValidationRule should only be used with the LCF Validator");
    }

    public function message()
    {
        throw new \Exception("FieldValidationRule should only be used with the LCF Validator");
    }

    public function validate(string $attribute, $value, &$messages, Validator $validator)
    {
        $this->field->validate($attribute, $value, $messages, $validator);
    }
}
