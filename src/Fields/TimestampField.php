<?php

namespace MadisonSolutions\LCF\Fields;

use DateTime;
use Carbon\Carbon;
use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\ScalarField;
use MadisonSolutions\LCF\Validator;

class TimestampField extends ScalarField
{
    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->options['max'] = ($this->options['max'] ? Carbon::parse($this->options['max']) : null);
        $this->options['min'] = ($this->options['min'] ? Carbon::parse($this->options['min']) : null);
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
        $rules['max'] = 'nullable|date';
        $rules['min'] = 'nullable|date';
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'lcf-timestamp-input';
    }

    public function validateNotNull(string $path, $value, &$messages, ?Validator $validator = null)
    {
        if (! ($value instanceof Carbon)) {
            $messages[$path][] = "Invalid value";
            return;
        }
        if ($this->max && $value > $this->max) {
            $messages[$path][] = "Maximum value is {$this->max}";
        }
        if ($this->min && $value < $this->min) {
            $messages[$path][] = "Minimum value is {$this->min}";
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        if ($input instanceof Carbon) {
            $output = $input;
            return true;
        }
        if ($input instanceof DateTime) {
            $output = Carbon::make($input);
            return true;
        }
        if (is_numeric($input) && Coerce::toInt($input, $timestamp)) {
            $output = Carbon::createFromTimestamp($timestamp);
            return true;
        }
        if (is_string($input)) {
            if ($input == '') {
                $output = null;
                return true;
            }
            // Accept only the format that is output by Carbon when json_encoded
            // eg 2019-06-07T13:19:04.202649Z
            if (preg_match('/^\d\d\d\d-\d\d-\d\dT\d\d:\d\d:\d\d(\.\d+)?Z$/', $input)) {
                $timestamp = strtotime($input);
                if ($timestamp !== false) {
                    $output = Carbon::createFromTimestamp($timestamp);
                    return true;
                }
            }
        }
        $output = ($keep_invalid ? $input : null);
        return false;
    }
}
