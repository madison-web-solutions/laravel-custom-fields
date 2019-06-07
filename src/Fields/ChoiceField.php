<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\ScalarField;
use MadisonSolutions\LCF\Validator;

class ChoiceField extends ScalarField
{
    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        $defaults['input'] = 'select';
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['choices'] = ['required', 'array', 'min:1'];
        $rules['choices.*'] = 'required|string';
        $rules['input'] = 'required|in:select,radio,search';
        return $rules;
    }

    public function inputComponent() : string
    {
        switch ($this->input) {
            case 'radio':
                return 'lcf-radio-input';
            case 'search':
                return 'lcf-search-input';
            case 'select':
            default:
                return 'lcf-select-input';
        }
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        // PHP arrays are ordered, whereas JSON objects are not.
        // We want to make sure we preserve the order of choices,
        // so convert to an array of objects with value, and label properties
        $data['settings']['choices'] = array_map(function ($value, $label) {
            return ['value' => $value, 'label' => $label];
        }, array_keys($this->choices), array_values($this->choices));
        $data['settings']['keys'] = array_keys($this->choices);
        return $data;
    }

    public function validateNotNull(string $path, $value, &$messages, ?Validator $validator = null)
    {
        if (! is_string($value) && ! is_int($value)) {
            $messages[$path][] = "Invalid value";
            return;
        }
        if ($value === '') {
            if ($this->options['required']) {
                $messages[$path][] = "This field is required";
            }
            return;
        }
        if (! array_key_exists($value, $this->choices)) {
            $messages[$path][] = "Invalid value";
            return;
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        if (Coerce::toArrayKey($input, $output)) {
            if (array_key_exists($output, $this->choices)) {
                return true;
            }
        }
        $output = ($keep_invalid ? $input : null);
        return false;
    }

    public function getSuggestions(string $search)
    {
        $search = strtolower($search);
        $suggestions = [];
        $count = 0;
        foreach ($this->choices as $key => $label) {
            if (str_contains(strtolower($key), $search) || str_contains(strtolower($label), $search)) {
                $suggestions[] = [
                    'id' => $key,
                    'label' => $label,
                ];
                $count++;
                if ($count >= 10) {
                    break;
                }
            }
        }
        return $suggestions;
    }

    public function getDisplayName($key)
    {
        return $this->choices[$key] ?? '';
    }
}
