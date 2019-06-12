<?php

namespace MadisonSolutions\LCF\Fields;

use DateTime;
use MadisonSolutions\JustDate\JustDate;
use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\ScalarField;
use MadisonSolutions\LCF\Validator;

class DateField extends ScalarField
{
    public function __construct(array $options)
    {
        parent::__construct($options);
        $this->options['max'] = ($this->options['max'] ? JustDate::fromYmd($this->options['max']) : null);
        $this->options['min'] = ($this->options['min'] ? JustDate::fromYmd($this->options['min']) : null);
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
        $date_rule = function ($attribute, $value, $fail) {
            try {
                JustDate::parseYmd($value);
            } catch (\Exception $e) {
                $fail("{$attribute} must be a date in Y-m-d format");
            }
        };
        $rules = parent::optionRules();
        $rules['max'] = ['nullable', 'string', $date_rule];
        $rules['min'] = ['nullable', 'string', $date_rule];
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'lcf-date-input';
    }

    public function validateNotNull(string $path, $value, &$messages, ?Validator $validator = null)
    {
        if (! ($value instanceof JustDate)) {
            $messages[$path][] = $this->trans('invalid');
            return;
        }
        if ($this->max && $value->isAfter($this->max)) {
            $messages[$path][] = $this->trans('max', ['max' => $this->max->format('d/m/Y')]);
        }
        if ($this->min && $value->isBefore($this->min)) {
            $messages[$path][] = $this->trans('min', ['min' => $this->min->format('d/m/Y')]);
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        // @todo timezones?
        if ($input instanceof JustDate) {
            $output = $input;
            return true;
        }
        if ($input instanceof DateTime) {
            $output = JustDate::fromDateTime($input);
            return true;
        }
        if (Coerce::toInt($input, $int_val, Coerce::REJECT_BOOL)) {
            $output = JustDate::fromTimestamp($int_val);
            return true;
        }
        if (is_string($input)) {
            try {
                $output = JustDate::fromYmd($input);
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
