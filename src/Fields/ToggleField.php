<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\ScalarField;

class ToggleField extends ScalarField
{
    public function inputComponent() : string
    {
        return 'lcf-toggle-input';
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

    public function validateNotNull(string $path, $value, &$messages, array $data)
    {
        if (! is_bool($value)) {
            $messages[$path][] = $this->trans('invalid');
            return;
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
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
            $output = ($keep_invalid ? $input : null);
            return false;
        }
    }
}
