<?php

namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\Field;
use MadisonSolutions\LCF\LinkValue;

class LinkField extends Field
{
    protected $store_in_json = true;

    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        $defaults['with_label'] = true;
        unset($defaults['default']);
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['with_label'] = 'required|boolean';
        return $rules;
    }

    public function fieldComponent() : string
    {
        return 'lcf-object-field';
    }

    public function inputComponent() : string
    {
        return 'lcf-link-input';
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        $data['settings']['keys'] = ['manual', 'link_id', 'url', 'label'];
        return $data;
    }

    public function validateNotNull(string $path, $value, &$messages, array $data)
    {
        if (! ($value instanceof LinkValue)) {
            $messages[$path][] = $this->trans('invalid');
            return;
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
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
                // If the key 'manual' is set, then this must dictate whether to create a link from a manual url of from a link_id
                if (Coerce::toBool($input['manual'], $manual)) {
                    if ($manual) {
                        $output = LinkValue::fromManual((string) ($input['url'] ?? ''), (string) ($input['label'] ?? ''));
                        return true;
                    } else {
                        $output = LinkValue::fromLinkId((string) ($input['link_id'] ?? ''), (string) ($input['label'] ?? ''));
                        return true;
                    }
                }
            } else {
                // Otherwise we'll just look to see whether the obj or url keys are set
                if (array_key_exists('link_id', $input)) {
                    $output = LinkValue::fromLinkId((string) $input['link_id'], (string) ($input['label'] ?? ''));
                    return true;
                } elseif (array_key_exists('url', $input)) {
                    $output = LinkValue::fromManual((string) $input['url'], (string) ($input['label'] ?? ''));
                    return true;
                }
            }
        }
        $output = ($keep_invalid ? $input : null);
        return false;
    }
}
