<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\ScalarField;
use MadisonSolutions\LCF\Validator;

class TextAreaField extends ScalarField
{
    public function inputComponent() : string
    {
        return 'lcf-text-area-input';
    }

    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        $defaults['lines'] = 5;
        $defaults['max'] = null;
        $defaults['min'] = null;
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['lines'] = 'integer|min:1';
        $rules['max'] = 'nullable|integer';
        $rules['min'] = 'nullable|integer';
        return $rules;
    }

    public function validateNotNull(string $path, $value, &$messages, ?Validator $validator = null)
    {
        if (! is_string($value)) {
            $messages[$path][] = "Invalid value";
            return;
        }
        if ($this->options['required'] && $value === '') {
            $messages[$path][] = "This field is required";
            return;
        }
        if (Coerce::toInt($this->options['max'], $max_int) && strlen($value) > $max_int) {
            $messages[$path][] = "Maximum length is {$max_int} characters";
        }
        if (Coerce::toInt($this->options['min'], $min_int) && strlen($value) < $min_int) {
            $messages[$path][] = "Minumum length is {$min_int} characters";
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        if (! Coerce::toString($input, $output)) {
            $output = ($keep_invalid ? $input : null);
            return false;
        }
        if ($output === '') {
            $output = null;
        }
        return true;
    }
}
