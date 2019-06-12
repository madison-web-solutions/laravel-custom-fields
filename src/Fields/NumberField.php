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
        $rules['decimals'] = 'nullable|integer';
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'lcf-number-input';
    }

    public function validateNotNull(string $path, $value, &$messages, ?Validator $validator = null)
    {
        if (! (is_int($value) || (is_float($value) && is_finite($value)))) {
            $messages[$path][] = $this->trans('invalid');
            return;
        }
        if (Coerce::toFloat($this->options['max'], $max_float) && $value > $max_float) {
            $messages[$path][] = $this->trans('max', ['max' => $max_float]);
        }
        if (Coerce::toFloat($this->options['min'], $min_float) && $value < $min_float) {
            $messages[$path][] = $this->trans('min', ['min' => $min_float]);
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

    protected function applyInputTransformationsNotNull($cast_value)
    {
        if (Coerce::toInt($this->options['decimals'], $decimals)) {
            $cast_value = round($cast_value, $decimals);
            if ($decimals <= 0) {
                $cast_value = (int) $cast_value;
            }
        }
        return $cast_value;
    }
}
