<?php
namespace MadisonSolutions\LCF;

use MadisonSolutions\Coerce\Coerce;

class TextAreaField extends Field
{
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

    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = 'string';
        $max = $this->options['max'];
        if (is_int($max)) {
            $rules[] = "max:{$max}";
        }
        $min = $this->options['min'];
        if (is_int($min)) {
            $rules[] = "min:{$min}";
        }
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'text-area-input';
    }

    protected function testTypeNotNull($input) : bool
    {
        return is_string($input);
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        if (! Coerce::toString($input, $output)) {
            return false;
        }
        if ($output === '') {
            $output = null;
        }
        return true;
    }
}
