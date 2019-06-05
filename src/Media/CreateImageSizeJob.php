<?php

namespace MadisonSolutions\LCF\Media;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Intervention\Image\ImageManager;

class CreateImageSizeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $item;
    protected $size;

    public function __construct(StorageItem $item, ImageSize $size)
    {
        $this->item = $item;
        $this->size = $size;
    }

    public function handle()
    {
        $location = $this->item->location($this->size);
        $this->item->deleteFile($this->size);
        $imageManager = new ImageManager(['driver' => 'imagick']);
        $image = $imageManager->make($this->item->fileContents());
        $resized = $this->size->resize($image);
        $encoded = $this->size->encode($resized);
        $this->item->disk()->put($location, $encoded);
    }
}
