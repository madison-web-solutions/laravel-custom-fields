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
            'url' => $this->url(),
            'title' => $this->title,
            'alt' => $this->alt,
            'mimeType' => ($type ? $type->mimeType : null),
            'category' => ($type ? $type->category : null),
            'orig' => $this->url(),
            'thumb' => ($type->sizable ? $this->urlOrCreate(self::thumbSize()) : null),
        ];
        return $out;
    }
}
