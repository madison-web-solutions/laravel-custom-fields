<?php
namespace MadisonSolutions\LCF;

use MadisonSolutions\Coerce\Coerce;

class TextField extends Field
{
    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        $defaults['max'] = null;
        $defaults['min'] = null;
        $defaults['regex'] = null;
        $defaults['content'] = null;
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['max'] = 'nullable|integer';
        $rules['min'] = 'nullable|integer';
        $rules['regex'] = 'nullable|string';
        $rules['content'] = 'nullable|in:email,ip,ipv4,ipv6,url,uuid';
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
        $content_rule = $this->options['content'];
        if ($content_rule) {
            $rules[] = $content_rule;
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
            $output = null;
            return false;
        }
        if ($output === '') {
            $output = null;
        }
        return true;
    }
}
