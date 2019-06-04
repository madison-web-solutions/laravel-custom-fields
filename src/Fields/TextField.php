<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\ScalarField;
use MadisonSolutions\LCF\Validator;

class TextField extends ScalarField
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

    public function validateNotNull(string $path, $value, &$messages, ?Validator $validator = null)
    {
        if (! is_string($value)) {
            $messages[$path][] = "Invalid value";
            return;
        }
        if ($value === '') {
            if ($this->options['required']) {
                $messages[$path][] = "This field is required";
            }
            return ;
        }
        if (Coerce::toInt($this->options['max'], $max_int) && strlen($value) > $max_int) {
            $messages[$path][] = "Maximum length is {$max_int} characters";
        }
        if (Coerce::toInt($this->options['min'], $min_int) && strlen($value) < $min_int) {
            $messages[$path][] = "Minumum length is {$min_int} characters";
        }
        $regex = $this->options['regex'];
        if ($regex && ! preg_match($regex, $value)) {
            $messages[$path][] = "Invalid value";
        }
        $content_rule = $this->options['content'];
        switch ($content_rule) {
            case 'url':
                if (! $validator->validateUrl('', $value)) {
                    $messages[$path][] = "Invalid url";
                }
                break;
            case 'uuid':
                if (! $validator->validateUuid('', $value)) {
                    $messages[$path][] = "Invalid uuid";
                }
                break;
            case 'email':
                if (! $validator->validateEmail('', $value)) {
                    $messages[$path][] = "Invalid email";
                }
                break;
            case 'ip':
                if (! $validator->validateIp('', $value)) {
                    $messages[$path][] = "Invalid ip address";
                }
                break;
            case 'ipv4':
                if (! $validator->validateIpv4('', $value)) {
                    $messages[$path][] = "Invalid ipv4 address";
                }
                break;
            case 'ipv6':
                if (! $validator->validateIpv6('', $value)) {
                    $messages[$path][] = "Invalid ipv6 address";
                }
                break;
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
