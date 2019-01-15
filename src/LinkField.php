<?php
namespace MadisonSolutions\LCF;

use Illuminate\Support\MessageBag;
use MadisonSolutions\Coerce\Coerce;
use Validator;

class LinkField extends Field
{
    protected $store_in_json = true;

    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        unset($defaults['default']);
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'link-input';
    }

    protected function testTypeNotNull($input) : bool
    {
        return ($input instanceof LinkValue);
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        if ($input instanceof LinkValue) {
            $output = $input;
            return true;
        }
        if (is_string($input)) {
            $output = LinkValue::fromManual($input);
            return true;
        }
        if (is_array($input)) {
            if (array_key_exists('manual', $input)) {
                // If the key 'manual' is set, then this must dictate whether to create a link from a manual url of from an obj spec
                if (Coerce::toBool($input['manual'], $manual)) {
                    if ($manual) {
                        $output = LinkValue::fromManual((string) ($input['url'] ?? ''), (string) ($input['label'] ?? ''));
                        return true;
                    } else {
                        $output = LinkValue::fromObjSpec((string) ($input['obj'] ?? ''), (string) ($input['label'] ?? ''));
                        return true;
                    }
                }
            } else {
                // Otherwise we'll just look to see whether the obj or url keys are set
                if (array_key_exists('obj', $input)) {
                    $output = LinkValue::fromObjSpec((string) $input['obj'], (string) ($input['label'] ?? ''));
                    return true;
                } elseif (array_key_exists('url', $input)) {
                    $output = LinkValue::fromManual((string) $input['url'], (string) ($input['label'] ?? ''));
                    return true;
                }
            }
        }
        return false;
    }

    protected function toPrimitiveNotNull($cast_value)
    {
        return $cast_value->toArray();
    }

    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = 'array';
        return $rules;
    }

    public function validate(array $data, string $path, MessageBag $messages)
    {
        $rules = [
            $path => $this->getValidationRules(),
            "{$path}.manual" => 'required|in:true,false',
            "{$path}.label" => 'nullable|string',
        ];
        $manual = data_get($data, "{$path}.manual");
        if ($manual === 'true') {
            $rules["{$path}.url"] = 'required|string';
        } else {
            $rules["{$path}.obj"] = 'required|string';
        }
        $validator = Validator::make($data, $rules);
        $messages->merge($validator->messages());
    }
}
