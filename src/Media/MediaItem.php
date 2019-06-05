<?php
namespace MadisonSolutions\LCF\Media;

use Illuminate\Database\Eloquent\Model;
use \Illuminate\Support\Str;

class MediaItem extends Model
{
    // Define database table
    protected $table = 'lcf_media';

    protected $fillable = ['title', 'extension', 'alt'];

    protected $storage_item;

    public function setUniqueSlug()
    {
        $base = Str::slug($this->title);

        for ($try = 1; $try < 10000; $try++) {
            $slug = $base . ( $try == 1 ? '' : "-{$try}" );
            if (! MediaItem::where('slug', $slug)->exists()) {
                $this->slug = $slug;
                return;
            }
        }

        throw new \Exception("Exhausted 10000 attempts to generate a slug for {$this->title}");
    }

    public function getStorageItem()
    {
        if (is_null($this->storage_item)) {
            $this->storage_item = new StorageItem($this->slug, $this->extension, config('lcf.media_dir_name', 'lcf_media'), config('lcf.media_disk_name', 'public'));
        }
        return $this->storage_item;
    }

    public function getTypeAttribute()
    {
        return new MediaType($this->extension);
    }

    public function url($size = null)
    {
        return $this->getStorageItem()->url($size);
    }

    public function urlOrCreate($size = null)
    {
        return $this->getStorageItem()->urlOrCreate($size);
    }

    public function delete()
    {
        $this->getStorageItem()->deleteFile();
        parent::delete();
    }
}
