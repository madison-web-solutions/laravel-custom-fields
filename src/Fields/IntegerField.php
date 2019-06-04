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

    public function validateNotNull(string $path, $value, &$messages, ?Validator $validator = null)
    {
        if (! is_int($value)) {
            $messages[$path][] = "Invalid value";
            return;
        }
        if (Coerce::toInt($this->options['max'], $max_int) && $value > $max_int) {
            $messages[$path][] = "Maximum value is {$max_int}";
        }
        if (Coerce::toInt($this->options['min'], $min_int) && $value < $min_int) {
            $messages[$path][] = "Minumum value is {$min_int}";
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
