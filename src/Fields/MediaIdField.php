<?php
namespace MadisonSolutions\LCF\Fields;

use MadisonSolutions\Coerce\Coerce;
use MadisonSolutions\LCF\ScalarField;
use MadisonSolutions\LCF\LCF;
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

    public function validateNotNull(string $path, $value, &$messages, array $data)
    {
        if (! is_int($value)) {
            $messages[$path][] = $this->trans('invalid');
            return;
        }
        $query = MediaItem::where('id', $value);
        if ($this->category) {
            $query->whereIn('extension', MediaType::allExtensionsForCategory($this->category));
        }
        if (! $query->exists()) {
            if ($this->category) {
                $messages[$path][] = $this->trans('missing-category', ['id' => $value, 'category' => mb_strtolower($this->category)]);
            } else {
                $messages[$path][] = $this->trans('missing', ['id' => $value]);
            }
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

    protected static $load_queue = [];
    protected static $loaded = [];

    public static function queueItemToLoad($id)
    {
        if (isset(self::$loaded[$id])) {
            return;
        }
        self::$load_queue[] = $id;
    }

    public static function fetchQueuedItems()
    {
        if (empty(self::$load_queue)) {
            return;
        }
        foreach (MediaItem::findMany(self::$load_queue) as $item) {
            self::$loaded[$item->id] = $item;
        }
        self::$load_queue = [];
    }

    public static function getQueuedItem($id)
    {
        return self::$loaded[$id] ?? null;
    }

    protected function expandPrepareNotNull($cast_value)
    {
        self::queueItemToLoad($cast_value);
    }

    protected function doExpandNotNull($cast_value)
    {
        self::fetchQueuedItems();
        return self::getQueuedItem($cast_value);
    }

    protected function expandKey(string $key)
    {
        return preg_replace('/_id$/', '', $key);
    }
}
