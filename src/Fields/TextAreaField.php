<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\ScalarField;

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

    public function validateNotNull(string $path, $value, &$messages, array $data)
    {
        if (! is_string($value)) {
            $messages[$path][] = $this->trans('invalid');
            return;
        }
        if ($value === '') {
            if ($this->options['required']) {
                $messages[$path][] = $this->trans('required');
            }
            return;
        }
        if (Coerce::toInt($this->options['max'], $max_int) && strlen($value) > $max_int) {
            $messages[$path][] = $this->trans('max', ['max' => $max_int]);
        }
        if (Coerce::toInt($this->options['min'], $min_int) && strlen($value) < $min_int) {
            $messages[$path][] = $this->trans('min', ['min' => $min_int]);
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        if (Coerce::toString($input, $output)) {
            return true;
        }
        $output = ($keep_invalid ? $input : null);
        return false;
    }
}
