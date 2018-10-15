<?php
namespace MadisonSolutions\LCF;

use MadisonSolutions\LCF\Media\MediaItem;

class MediaField extends Field
{
    public function optionRules() : array
    {
        $rules = parent::optionRules();
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'media-input';
    }

    public function getValidationRules()
    {
        $rules = parent::getValidationRules();
        $rules[] = 'integer';
        $rules[] = function ($attribute, $value, $fail) {
            $query = MediaItem::where('id', $value);
            if (! $query->exists()) {
                $fail("Invalid reference for {$attribute}");
            }
        };
        return $rules;
    }

    protected function testTypeNotNull($input) : bool
    {
        return ($input instanceof MediaItem);
    }

    protected function coerceNotNull($input, &$output, int $on_fail) : bool
    {
        if ($input instanceof MediaItem) {
            $output = $input;
            return true;
        }
        $obj = MediaItem::find($input);
        if ($obj) {
            $output = $obj;
            return true;
        }
        return false;
    }

    protected function toPrimitiveNotNull($cast_value)
    {
        return $cast_value->id;
    }
}
