<?php
namespace MadisonSolutions\LCF;

use MadisonSolutions\Coerce\Coerce;
use DateTime;

class TimestampField extends Field
{
    public function inputComponent() : string
    {
        return 'timestamp-input';
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

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
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
        return false;
    }
}
