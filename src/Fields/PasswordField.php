<?php

namespace MadisonSolutions\LCF\Fields;

use Illuminate\Support\Str;
use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\PasswordStrengthTester;
use MadisonSolutions\LCF\ScalarField;

class PasswordField extends ScalarField
{
    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        $defaults['strength'] = null;
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['strength'] = 'nullable|integer|min:1|max:5';
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'lcf-password-input';
    }

    public function validateNotNull(string $path, $value, &$messages, array $data)
    {
        if (! is_string($value)) {
            $messages[$path][] = $this->trans('invalid');
            return;
        }
        if ($value === '') {
            if ($this->options['required']) {
                $messages[$path][] = $this->trans('required');
            }
            return;
        }
        if (Coerce::toInt($this->options['strength'], $strength)) {
            $score = PasswordStrengthTester::score($value);
            if ($score < $strength) {
                $messages[$path][] = $this->trans('weak');
                return;
            }
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        if (Coerce::toString($input, $output)) {
            return true;
        }
        $output = ($keep_invalid ? $input : null);
        return false;
    }
}
