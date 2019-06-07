<?php

namespace MadisonSolutions\LCF\Fields;

use DateTime;
use MadisonSolutions\JustDate\JustTime;
use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\ScalarField;
use MadisonSolutions\LCF\Validator;

class TimeField extends ScalarField
{
    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->options['max'] = ($this->options['max'] ? JustTime::fromHis($this->options['max']) : null);
        $this->options['min'] = ($this->options['min'] ? JustTime::fromHis($this->options['min']) : null);
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
        $time_rule = function ($attribute, $value, $fail) {
            try {
                JustTime::parseHis($value);
            } catch (\Exception $e) {
                $fail("{$attribute} must be a date in H:i:s format");
            }
        };
        $rules = parent::optionRules();
        $rules['max'] = ['nullable', 'string', $time_rule];
        $rules['min'] = ['nullable', 'string', $time_rule];
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'lcf-time-input';
    }

    public function validateNotNull(string $path, $value, &$messages, ?Validator $validator = null)
    {
        if (! ($value instanceof JustTime)) {
            $messages[$path][] = "Invalid value";
            return;
        }
        if ($this->max && $value->isAfter($this->max)) {
            $messages[$path][] = "Maximum value is {$this->max}";
        }
        if ($this->min && $value->isBefore($this->min)) {
            $messages[$path][] = "Minimum value is {$this->min}";
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        if ($input instanceof JustTime) {
            $output = $input;
            return true;
        }
        if ($input instanceof DateTime) {
            $output = JustTime::fromDateTime($input);
            return true;
        }
        if (is_string($input)) {
            try {
                $output = JustTime::fromHis($input);
                return true;
            } catch (\Exception $e) {
                $output = ($keep_invalid ? $input : null);
                return false;
            }
        }
        $output = ($keep_invalid ? $input : null);
        return false;
    }
}
