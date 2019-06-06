<?php

namespace MadisonSolutions\LCF\Fields;

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
}
