<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\ScalarField;
use MadisonSolutions\LCF\Validator;

class IntegerField extends ScalarField
{
    public function inputComponent() : string
    {
        return 'lcf-number-input';
    }

    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        $defaults['max'] = null;
        $defaults['min'] = null;
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['max'] = 'nullable|integer';
        $rules['min'] = 'nullable|integer';
        return $rules;
    }

    public function validateNotNull(string $path, $value, &$messages, Validator $validator)
    {
        if (! is_int($value)) {
            $messages[$path][] = "Invalid value";
            return;
        }
        $max = $this->options['max'];
        if (is_int($max) && $value > $max) {
            $messages[$path][] = "Maximum value is {$max}";
        }
        $min = $this->options['min'];
        if (is_int($min) && $value < $min) {
            $messages[$path][] = "Minumum value is {$min}";
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        if (! Coerce::toInt($input, $output)) {
            $output = ($keep_invalid ? $input : null);
            return false;
        }
        return true;
    }
}
