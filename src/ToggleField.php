<?php
namespace MadisonSolutions\LCF;

use MadisonSolutions\Coerce\Coerce;

class ToggleField extends Field
{
    public function inputComponent() : string
    {
        return 'toggle-input';
    }

    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        $defaults['true_label'] = 'On';
        $defaults['false_label'] = 'Off';
        $defaults['default'] = false;
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['true_label'] = 'required|string';
        $rules['false_label'] = 'required|string';
        $rules['default'] = 'required|boolean';
        return $rules;
    }

    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = 'in:true,false';
        return $rules;
    }

    protected function testTypeNotNull($input) : bool
    {
        return is_bool($input);
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        if (Coerce::toBool($input, $output)) {
            return true;
        } elseif ($input === $this->true_label) {
            $output = true;
            return true;
        } elseif ($input === $this->false_label) {
            $output = false;
            return true;
        } else {
            return false;
        }
    }
}
