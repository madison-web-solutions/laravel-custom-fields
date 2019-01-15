<?php
namespace MadisonSolutions\LCF;

use MadisonSolutions\Coerce\Coerce;

class MarkdownField extends Field
{
    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = 'string';
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'markdown-input';
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
