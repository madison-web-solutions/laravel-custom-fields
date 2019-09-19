<?php
namespace MadisonSolutions\LCF\Media;

use Illuminate\Http\Resources\Json\Resource;

class MediaItemResource extends Resource
{
    protected static $thumb_size;

    public static function thumbSize()
    {
        if (is_null(self::$thumb_size)) {
            self::$thumb_size = new ImageSize([
                'method' => 'fit',
                'width' => 200,
                'height' => 150,
            ]);
        }
        return self::$thumb_size;
    }

    public function toArray($request)
    {
        $type = $this->type;
        $out = [
            'id' => $this->id,
            'title' => $this->title,
            'alt' => $this->alt,
            'url' => ($this->fileExists() ? $this->url() : null),
            'extension' => $this->extension,
            'category' => ($type ? $type->category : null),
            'thumb' => ($this->isSizable() ? $this->urlOrCreate(self::thumbSize()) : null),
        ];
        return $out;
    }
}
