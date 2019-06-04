<?php

namespace MadisonSolutions\LCF;

use Illuminate\Validation\Validator as LaravelValidator;

class Validator extends LaravelValidator
{
    public static function make(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        return new Validator(validator()->getTranslator(), $data, $rules, $messages, $customAttributes);
    }

    /**
     * Validate an attribute using a custom rule object.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Illuminate\Contracts\Validation\Rule  $rule
     * @return void
     */
    protected function validateUsingCustomRule($attribute, $value, $rule)
    {
        if ($rule instanceof FieldValidationRule) {
            $rule->validate($attribute, $value, $messages, $this);
            foreach ($messages as $path => $path_messages) {
                $this->failedRules[$path][get_class($rule)] = [];
                foreach ($path_messages as $message) {
                    $this->messages->add($path, $this->makeReplacements($message, $attribute, get_class($rule), []));
                }
            }
        } else {
            return parent::validateUsingCustomRule($attribute, $value, $rule);
        }
    }

    protected function getSize($attribute, $value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        } else {
            return parent::getSize($attribute, $value);
        }
    }

    protected function getAttributeType($attribute)
    {
        $value = $this->getValue($attribute);
        if (is_int($value) || is_float($value)) {
            return 'numeric';
        } else {
            return parent::getAttributeType($attribute);
        }
    }
}
