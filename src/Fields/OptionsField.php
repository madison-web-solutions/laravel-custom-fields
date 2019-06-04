<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\Field;
use MadisonSolutions\LCF\Validator;

class OptionsField extends Field
{
    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['choices'] = ['required', 'array', 'min:1'];
        $rules['choices.*'] = 'required|string';
        return $rules;
    }

    public function fieldComponent() : string
    {
        return 'lcf-object-field';
    }

    public function inputComponent() : string
    {
        return 'lcf-options-input';
    }

    public function jsonSerialize()
    {
        return [
            'fieldComponent' => $this->fieldComponent(),
            'inputComponent' => $this->inputComponent(),
            'settings' => array_merge($this->options, ['keys' => array_keys($this->choices)]),
        ];
    }

    public function validateNotNull(string $path, $value, &$messages, Validator $validator)
    {
        if (! is_array($value)) {
            $messages[$path][] = "Invalid value";
            return;
        }
        foreach ($this->choices as $value => $label) {
            if (! is_bool($cast_value[$value] ?? null)) {
                $messages["{$path}.{$value}"][] = "Invalid value";
            }
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        if (! is_array($input)) {
            $output = ($keep_invalid ? $input : null);
            return false;
        }
        $output = [];
        $ok = true;
        foreach ($this->choices as $value => $label) {
            if (Coerce::toBool($input[$value] ?? false, $sub_output)) {
                $output[$value] = $sub_output;
            } else {
                $output[$value] = ($keep_invalid ? $input[$value] : null);
                $ok = false;
            }
        }
        return $ok;
    }
}
