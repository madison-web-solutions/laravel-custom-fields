<?php
namespace MadisonSolutions\LCF;

use MadisonSolutions\Coerce\Coerce;

class IntegerField extends Field
{
    public function inputComponent() : string
    {
        return 'number-input';
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

    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = 'integer';
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

    protected function testTypeNotNull($input) : bool
    {
        return is_int($input);
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        return Coerce::toInt($input, $output);
    }
}
