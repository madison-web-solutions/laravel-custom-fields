<?php
namespace MadisonSolutions\LCF;

class TextField extends Field
{
    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = 'string';
        return $rules;
    }

    protected function testTypeNotNull($input) : bool
    {
        return is_string($input);
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        if (! Coerce::toString($input, $output)) {
            return false;
        }
        if ($output === '') {
            $output = null;
        }
        return true;
    }
}
