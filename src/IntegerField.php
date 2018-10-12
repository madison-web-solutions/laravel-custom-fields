<?php
namespace MadisonSolutions\LCF;

class IntegerField extends Field
{
    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = 'integer';
        return $rules;
    }

    protected function testTypeNotNull($input) : bool
    {
        return is_int($input);
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        return Coerce::toInt($input, $output);
    }
}
