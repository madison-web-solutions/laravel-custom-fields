<?php

namespace MadisonSolutions\LCF\Fields;

use Illuminate\Support\Str;
use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\ScalarField;

class TextField extends ScalarField
{
    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        $defaults['max'] = null;
        $defaults['min'] = null;
        $defaults['regex'] = null;
        $defaults['content'] = null;
        $defaults['case'] = null;
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['max'] = 'nullable|integer';
        $rules['min'] = 'nullable|integer';
        $rules['regex'] = 'nullable|string';
        $rules['content'] = 'nullable|in:email,ip,ipv4,ipv6,url,uuid';
        $rules['case'] = 'nullable|in:lower,upper,title,slug';
        return $rules;
    }

    protected function borrowLaravelValdator()
    {
        // Laravel has lots of useful text validation method in the ValidatesAttributes trait
        // In order to access them for one-off use,
        // we can just return an anonymous class object that uses the trait.
        return new class {
            use \Illuminate\Validation\Concerns\ValidatesAttributes;
        };
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
        if (Coerce::toInt($this->options['max'], $max_int) && strlen($value) > $max_int) {
            $messages[$path][] = $this->trans('max', ['max' => $max_int]);
        }
        if (Coerce::toInt($this->options['min'], $min_int) && strlen($value) < $min_int) {
            $messages[$path][] = $this->trans('min', ['min' => $min_int]);
        }
        $regex = $this->options['regex'];
        if ($regex && ! preg_match($regex, $value)) {
            $messages[$path][] = $this->trans('invalid');
        }
        $content_rule = $this->options['content'];
        switch ($content_rule) {
            case 'url':
                if (! $this->borrowLaravelValdator()->validateUrl('', $value)) {
                    $messages[$path][] = $this->trans('invalid-url');
                }
                break;
            case 'uuid':
                if (! $this->borrowLaravelValdator()->validateUuid('', $value)) {
                    $messages[$path][] = $this->trans('invalid-uuid');
                }
                break;
            case 'email':
                if (! $this->borrowLaravelValdator()->validateEmail('', $value, [])) {
                    $messages[$path][] = $this->trans('invalid-email');
                }
                break;
            case 'ip':
                if (! $this->borrowLaravelValdator()->validateIp('', $value)) {
                    $messages[$path][] = $this->trans('invalid-ip');
                }
                break;
            case 'ipv4':
                if (! $this->borrowLaravelValdator()->validateIpv4('', $value)) {
                    $messages[$path][] = $this->trans('invalid-ipv4');
                }
                break;
            case 'ipv6':
                if (! $this->borrowLaravelValdator()->validateIpv6('', $value)) {
                    $messages[$path][] = $this->trans('invalid-ipv6');
                }
                break;
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
        switch ($this->options['case']) {
            case 'lower':
                $cast_value = mb_convert_case($cast_value, MB_CASE_LOWER);
                break;
            case 'upper':
                $cast_value = mb_convert_case($cast_value, MB_CASE_UPPER);
                break;
            case 'title':
                $cast_value = mb_convert_case($cast_value, MB_CASE_TITLE);
                break;
            case 'slug':
                $cast_value = Str::slug($cast_value);
                break;
        }
        return $cast_value;
    }
}
