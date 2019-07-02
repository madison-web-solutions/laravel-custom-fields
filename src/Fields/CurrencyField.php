<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\Coerce\Coerce;

class CurrencyField extends IntegerField
{
    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        $defaults['symbol'] = '£';
        $defaults['symbol_placement'] = 'before';
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['symbol'] = 'nullable|string|max:3';
        $rules['symbol_placement'] = 'nullable|in:before,after';
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'lcf-currency-input';
    }

    public function format(int $value)
    {
        $value_f = number_format($value / 100, 2, '.', ',');
        switch ($this->symbol_placement) {
            case 'before':
                return $this->symbol . $value_f;
            case 'after':
                return $value_f . $this->symbol;
            default:
                return $value_f;
        }
    }

    public function validateNotNull(string $path, $value, &$messages, array $data)
    {
        if (! is_int($value)) {
            $messages[$path][] = $this->trans('invalid');
            return;
        }
        if (Coerce::toInt($this->options['max'], $max_int) && $value > $max_int) {
            $messages[$path][] = $this->trans('max', ['max' => $this->format($max_int)]);
        }
        if (Coerce::toInt($this->options['min'], $min_int) && $value < $min_int) {
            $messages[$path][] = $this->trans('min', ['min' => $this->format($min_int)]);
        }
    }
}
