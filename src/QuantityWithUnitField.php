<?php
namespace MadisonSolutions\LCF;

class QuantityWithUnitField extends Field
{
    protected $units = [
        'm' => [
            'name' => 'Metre',
            'plural' => 'Metres',
            'dimension' => 'L',
            'factor' => 1,
            'family' => 'metric',
            'symbol' => 'm',
        ],
        'cm' => [
            'name' => 'Centimetre',
            'plural' => 'Centimetres',
            'dimension' => 'L',
            'factor' => 0.01,
            'family' => 'metric',
            'symbol' => 'cm',
        ],
        'mm' => [
            'name' => 'Millimetre',
            'plural' => 'Millimetres',
            'dimension' => 'L',
            'factor' => 0.001,
            'family' => 'metric',
            'symbol' => 'mm',
        ],
        'km' => [
            'name' => 'Kilometre',
            'plural' => 'Kilometres',
            'dimension' => 'L',
            'factor' => 1000,
            'family' => 'metric',
            'symbol' => 'km',
        ],
        'inch' => [
            'name' => 'Inch',
            'plural' => 'Inches',
            'dimension' => 'L',
            'factor' => 0.0254,
            'family' => 'imperial',
            'symbol' => '"',
        ],
        'm2' => [
            'name' => 'Metre Squared',
            'plural' => 'Metres Squared',
            'dimension' => 'LL',
            'factor' => 1,
            'family' => 'metric',
            'symbol' => 'm²',
        ],
    ];

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
            $output = null;
            return false;
        }
    }
}
