<?php
namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\ScalarField;
use MadisonSolutions\LCF\LCF;
use MadisonSolutions\LCF\Validator;
use MadisonSolutions\LCF\Media\MediaItem;
use MadisonSolutions\LCF\Media\MediaType;

class MediaIdField extends ScalarField
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
        return 'lcf-media-input';
    }

    public function validateNotNull(string $path, $value, &$messages, ?Validator $validator = null)
    {
        if (! is_int($value)) {
            $messages[$path][] = "Invalid value";
            return;
        }
        $query = MediaItem::where('id', $value);
        if ($this->category) {
            $query->whereIn('extension', MediaType::allExtensionsForCategory($this->category));
        }
        if (! $query->exists()) {
            $messages[$path][] = "Model not found with id {$value}";
        }
    }

    protected function coerceNotNull($input, &$output, bool $keep_invalid = false) : bool
    {
        if ($input instanceof MediaItem) {
            $output = $input->id;
            return true;
        }
        if (Coerce::toInt($input, $output, Coerce::REJECT_BOOL)) {
            return true;
        }
        $output = ($keep_invalid ? $input : null);
        return false;
    }
}
