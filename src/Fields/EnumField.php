<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\Enum\Enum;
use MadisonSolutions\LCF\ScalarField;

class EnumField extends ScalarField
{
    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        $defaults['input'] = 'select';
        $defaults['input_layout'] = 'horizontal';
        $defaults['label_attribute'] = 'label';
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['enum_class'] = ['required', function($attribute, $value, $fail) {
            if (! is_string($value) || ! is_a($value, Enum::class, true)) {
                $fail($attribute.' is not an Enum class');
            }
        }];
        $rules['input'] = 'required|in:select,radio,search';
        $rules['input_layout'] = 'nullable|in:horizontal,vertical';
        $rules['label_attribute'] = 'required|string';
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
        $choices = [];
        $class = $this->enum_class;
        $label = $this->label_attribute;
        foreach ($class::members() as $value => $member) {
            $choices[] = ['value' => $value, 'label' => $member->$label];
        }

        $data['settings']['choices'] = $choices;
        $data['settings']['keys'] = array_keys($choices);
        return $data;
    }

    public function validateNotNull(string $path, $value, &$messages, array $data)
    {
        if (! is_string($value) && ! is_int($value)) {
            $messages[$path][] = $this->trans('invalid');
            return;
        }
        if ($value === '') {
            if ($this->options['required']) {
                $messages[$path][] = $this->trans('required');
            }
            return;
        }
        $class = $this->enum_class;
        if (! $class::has($value)) {
            $messages[$path][] = $this->trans('not-in-choices');
            return;
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        $class = $this->enum_class;
        if (Coerce::toArrayKey($input, $output)) {
            if ($class::has($output)) {
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
        $class = $this->enum_class;
        $label = $this->label_attribute;
        foreach ($class::members() as $key => $member) {
            $label = $member->$label;
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
        $class = $this->enum_class;
        $label = $this->label_attribute;
        $member = $class::maybeNamed($key);
        return $member ? ($member->$label ?? '') : '';
    }
}
