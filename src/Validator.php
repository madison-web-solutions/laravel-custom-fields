<?php

namespace MadisonSolutions\LCF;

use Illuminate\Support\MessageBag;
use Illuminate\Validation\Validator as LaravelValidator;

class Validator extends LaravelValidator
{
    protected $fields;

    public function __construct(array $data = [], array $fields = [], array $rules = [])
    {
        parent::__construct(validator()->getTranslator(), $data, $rules, [], []);
        $this->fields = $fields;

        $app = app();
        if (isset($app['db'], $app['validation.presence'])) {
            $this->setPresenceVerifier($app['validation.presence']);
        }
    }

    /**
     * Determine if the data passes the validation rules.
     *
     * @return bool
     */
    public function passes()
    {
        $this->messages = new MessageBag;

        [$this->distinctValues, $this->failedRules] = [[], []];

        // Do LCF field validation first
        foreach ($this->fields as $field_name => $field) {
            $this->validateField($field_name, $field);
        }

        // We'll spin through each rule, validating the attributes attached to that
        // rule. Any error messages will be added to the containers with each of
        // the other error messages, returning true if we don't have messages.
        foreach ($this->rules as $attribute => $rules) {
            $attribute = str_replace('\.', '->', $attribute);

            foreach ($rules as $rule) {
                $this->validateAttribute($attribute, $rule);

                if ($this->shouldStopValidating($attribute)) {
                    break;
                }
            }
        }

        // Here we will spin through all of the "after" hooks on this validator and
        // fire them off. This gives the callbacks a chance to perform all kinds
        // of other validation that needs to get wrapped up in this operation.
        foreach ($this->after as $after) {
            call_user_func($after);
        }

        return $this->messages->isEmpty();
    }

    protected function validateField($field_name, $field)
    {
        $value = $this->getValue($field_name);
        $field->validate($field_name, $value, $field_messages, $this->getData());

        foreach ($field_messages as $path => $path_messages) {
            $this->failedRules[$path]['LCFField'] = [];
            foreach ($path_messages as $message) {
                $this->messages->add($path, $message);
            }
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
