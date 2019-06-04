<?php
namespace MadisonSolutions\LCF;

use MadisonSolutions\LCF\Media\MediaItem;
use MadisonSolutions\LCF\Media\MediaType;

class MediaField extends Field
{
    public function optionDefaults() : array
    {
        $defaults = parent::optionDefaults();
        $defaults['category'] = null;
        return $defaults;
    }

    public function optionRules() : array
    {
        $rules = parent::optionRules();
        $rules['category'] = 'nullable|in:image,document,spreadsheet,presentation';
        return $rules;
    }

    public function inputComponent() : string
    {
        return 'media-input';
    }

    public function getValidationRules()
    {
        $category = $this->options['category'];
        $rules = parent::getValidationRules();
        $rules[] = 'integer';
        $rules[] = function ($attribute, $value, $fail) use ($category) {
            $query = MediaItem::where('id', $value);
            if ($category) {
                $query->whereIn('extension', MediaType::allExtensionsForCategory($category));
            }
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
        $output = null;
        return false;
    }

    protected function toPrimitiveNotNull($cast_value)
    {
        return $cast_value->id;
    }
}
