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
        $data = parent::jsonSerialize();
        // PHP arrays are ordered, whereas JSON objects are not.
        // We want to make sure we preserve the order of choices,
        // so convert to an array of objects with key, and label properties
        $data['settings']['choices'] = array_map(function ($key, $label) {
            return ['key' => $key, 'label' => $label];
        }, array_keys($this->choices), array_values($this->choices));
        $data['settings']['keys'] = array_keys($this->choices);
        return $data;
    }

    public function validateNotNull(string $path, $value, &$messages, ?Validator $validator = null)
    {
        if (! is_array($value)) {
            $messages[$path][] = "Invalid value";
            return;
        }
        foreach ($this->choices as $key => $label) {
            if (! is_bool($value[$key] ?? null)) {
                $messages["{$path}.{$key}"][] = "Invalid value";
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
        foreach ($this->choices as $key => $label) {
            if (Coerce::toBool($input[$key] ?? false, $sub_output)) {
                $output[$key] = $sub_output;
            } else {
                $output[$key] = ($keep_invalid ? ($input[$key] ?? null) : null);
                $ok = false;
            }
        }
        return $ok;
    }
}
