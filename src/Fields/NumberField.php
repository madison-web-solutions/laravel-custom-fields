<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\ScalarField;
use MadisonSolutions\LCF\Validator;

class NumberField extends ScalarField
{
    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        $defaults['max'] = null;
        $defaults['min'] = null;
        $defaults['decimals'] = null;
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['max'] = 'nullable|numeric';
        $rules['min'] = 'nullable|numeric';
        $rules['decimals'] = 'nullable|integer|min:1';
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'lcf-number-input';
    }

    public function validateNotNull(string $path, $value, &$messages, ?Validator $validator = null)
    {
        if (! (is_int($value) || (is_float($value) && is_finite($value)))) {
            $messages[$path][] = "Invalid value";
            return;
        }
        if (Coerce::toFloat($this->options['max'], $max_float) && $value > $max_float) {
            $messages[$path][] = "Maximum value is {$max_float}";
        }
        if (Coerce::toFloat($this->options['min'], $min_float) && $value < $min_float) {
            $messages[$path][] = "Minumum value is {$min_float}";
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        if (Coerce::toInt($input, $output)) {
            return true;
        } elseif (Coerce::toFloat($input, $output)) {
            return true;
        } else {
            $output = ($keep_invalid ? $input : null);
            return false;
        }
    }
}