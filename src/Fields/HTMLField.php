<?php

namespace MadisonSolutions\LCF\Fields;

use Log;
use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\RestrictedHTMLFragment;
use MadisonSolutions\LCF\ScalarField;

class HTMLField extends ScalarField
{
    public function inputComponent() : string
    {
        return 'lcf-ckeditor-input';
    }

    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        return $rules;
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
        $output = $this->filterHtml($value, $warnings, $errors);
        if (! empty($errors) || ! empty($warnings)) {
            Log::warning("HTMLField invalid input", ['errors' => $errors, 'warnings' => $warnings]);
            $messages[$path][] = $this->trans('invalid');
            return;
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

    protected function applyInputTransformationsNotNull($cast_value)
    {
        return $this->filterHtml($cast_value);
    }

    protected function filterHtml(string $input, &$warnings = null, &$errors = null)
    {
        $frag = new RestrictedHTMLFragment();
        $output = $frag->toRestrictedHtml($input);
        $warnings = $frag->warnings;
        $errors = $frag->errors;
        return $output;
    }
}
