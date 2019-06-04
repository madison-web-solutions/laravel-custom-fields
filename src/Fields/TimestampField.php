<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\ScalarField;
use MadisonSolutions\LCF\Validator;
use DateTime;

class TimestampField extends ScalarField
{
    public function inputComponent() : string
    {
        return 'lcf-timestamp-input';
    }

    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        $defaults['type'] = 'date';
        $defaults['max'] = null;
        $defaults['min'] = null;
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['type'] = 'in:date,datetime,time';
        $rules['max'] = 'nullable|integer';
        $rules['min'] = 'nullable|integer';
        return $rules;
    }

    public function validateNotNull(string $path, $value, &$messages, Validator $validator)
    {
        if (! is_int($value)) {
            $messages[$path][] = "Invalid value";
            return;
        }
        $max = $this->options['max'];
        if (is_int($max) && $value > $max) {
            $messages[$path][] = "Maximum value is {$max}";
        }
        $min = $this->options['min'];
        if (is_int($min) && $value < $min) {
            $messages[$path][] = "Minumum value is {$min}";
        }
    }

    protected function truncate(int $timestamp) : int
    {
        $year = 1970;
        $month = 1;
        $day = 1;
        $hour = 0;
        $minute = 0;
        $second = 0;
        if ($this->type == 'date' || $this->type == 'datetime') {
            $year = (int) gmdate("Y", $timestamp);
            $month = (int) gmdate("m", $timestamp);
            $day = (int) gmdate("d", $timestamp);
        }
        if ($this->type == 'time' || $this->type == 'datetime') {
            $hour = (int) gmdate("G", $timestamp);
            $minute = (int) gmdate("i", $timestamp);
            $second = (int) gmdate("s", $timestamp);
        }
        return gmmktime($hour, $minute, $second, $month, $day, $year);
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        if (is_int($input)) {
            $output = $this->truncate($input);
            return true;
        }
        if ($input instanceof DateTime) {
            $output = $this->truncate($input->getTimestamp());
            return true;
        }
        if (is_string($input)) {
            $timestamp = strtotime($input);
            if ($timestamp === false) {
                $output = ($keep_invalid ? $input : null);
                return false;
            } else {
                $output = $this->truncate($timestamp);
                return true;
            }
        }
        if (Coerce::toInt($input, $timestamp)) {
            $output = $this->truncate($timestamp);
            return true;
        }
        $output = ($keep_invalid ? $input : null);
        return false;
    }
}
