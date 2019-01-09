<?php
namespace MadisonSolutions\LCF;

class QuantityWithUnitField extends Field
{
    public function inputComponent() : string
    {
        return 'text-input';
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['units'] = 'required|array';
        $rules['units.*'] = 'required|string';
        return $rules;
    }

    public function parseValue($value)
    {
        if (! is_string($value)) {
            return false;
        }
        $value = strtolower($value);
        foreach ($this->options['units'] as $unit) {
            if (ends_with($value, strtolower($unit))) {
                $numerical_part = trim(substr($value, 0, -strlen($unit)));
                if (is_numeric($numerical_part)) {
                    return [$numerical_part * 1, $unit];
                }
            }
        }
        return false;
    }

    public function correctUnitsRule()
    {
        return function ($attribute, $value, $fail) {
            $parsed = $this->parseValue($value);
            if (!$parsed) {
                $fail("Invalid value '{$value}', expected a quantity in one of these units: " . implode(', ', $this->options['units']));
            }
        };
    }

    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = $this->correctUnitsRule();
        return $rules;
    }

    protected function testTypeNotNull($input) : bool
    {
        return (bool) $this->parseValue($input);
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        $parsed = $this->parseValue($input);
        if ($parsed) {
            $output = $parsed[0] . $parsed[1];
            return true;
        } else {
            return false;
        }
    }
}
