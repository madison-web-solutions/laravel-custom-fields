<?php
namespace MadisonSolutions\LCF;

class TextField extends Field
{
    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        $defaults['max'] = null;
        $defaults['min'] = null;
        $defaults['regex'] = null;
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['max'] = 'nullable|integer';
        $rules['min'] = 'nullable|integer';
        $rules['regex'] = 'nullable|string';
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
        $regex = $this->options['regex'];
        if ($regex) {
            $rules[] = "regex:{$regex}";
        }
        return $rules;
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
