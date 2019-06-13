<?php
namespace MadisonSolutions\LCF;

use Illuminate\Validation\Rule;
use MadisonSolutions\Coerce\Coerce;

class ChoiceField extends Field
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
                return 'radio-input';
            case 'search':
                return 'search-input';
            case 'select':
            default:
                return 'select-input';
        }
    }

    public function jsonSerialize()
    {
        $output = parent::jsonSerialize();
        $choicesArr = [];
        foreach ($this->choices as $key => $label) {
            $choicesArr[] = ['key' => $key, 'label' => $label];
        }
        $output['options']['choices'] = $choicesArr;
        return $output;
    }

    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = 'string';
        $rules[] = Rule::in(array_keys($this->choices));
        return $rules;
    }

    protected function testTypeNotNull($input) : bool
    {
        return is_scalar($input) && array_key_exists($input, $this->choices);
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        if (Coerce::toArrayKey($input, $output)) {
            if (array_key_exists($output, $this->choices)) {
                return true;
            }
        }
        $output = null;
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
