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

    public function validateNotNull(string $path, $value, &$messages, Validator $validator)
    {
        if (! is_string($value)) {
            $messages[$path][] = "Invalid value";
            return;
        }
        $max = $this->options['max'];
        if (is_int($max) && strlen($value) > $max) {
            $messages[$path][] = "Maximum length is {$max} characters";
        }
        $min = $this->options['min'];
        if (is_int($min) && strlen($value) < $min) {
            $messages[$path][] = "Minumum length is {$min} characters";
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
