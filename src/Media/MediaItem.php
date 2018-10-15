<?php
namespace MadisonSolutions\LCF\Media;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use \Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Intervention\Image\ImageManager;
use Storage;

class MediaItem extends Model
{
    // Define database table
    protected $table = 'lcf_media';

    protected $fillable = ['title', 'extension', 'alt'];

    public function getTypeAttribute()
    {
        return new MediaType($this->extension);
    }

    public function disk()
    {
        return Storage::disk('public');
    }

    protected function fileName(?ImageSize $size = null)
    {
        if ($size) {
            if (! $this->type->sizable) {
                throw new \Exception("Media of type {$this->type->label} cannot be resized");
            }
            return $size->fileName($this->slug);
        } else {
            return "{$this->slug}.{$this->extension}";
        }
    }

    public function location(?ImageSize $size = null)
    {
        return 'lcf-media/' . $this->fileName($size);
    }

    public function url(?ImageSize $size = null)
    {
        return $this->disk()->url($this->location($size));
    }

    public function urlOrCreate(?ImageSize $size = null)
    {
        if (! $this->fileExists($size)) {
            try {
                $this->createImageSize($size);
            } catch (FileNotFoundException $e) {
                return false;
            }
        }
        return $this->url($size);
    }

    // throws Intervention\Image\Exception\NotReadableException
    public function createImageSize(ImageSize $size)
    {
        $this->deleteFile($size);
        $location = $this->location($size);
        $imageManager = new ImageManager(['driver' => 'imagick']);
        $image = $imageManager->make($this->fileContents());
        $this->disk()->put($this->location($size), $size->resize($image));
    }

    public function fileSize(?ImageSize $size = null)
    {
        return $this->disk()->size($this->location($size));
    }

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

    public function setFileFromUpload(UploadedFile $upload)
    {
        if (!$upload->isValid()) {
            throw new \Exception("Uploaded file not valid");
        }
        $this->setFileByPath($upload->getPathname());
    }

    public function setFileByPath(string $srcPath)
    {
        $this->deleteFile();
        $fh = fopen($srcPath, 'rb');
        $this->disk()->put($this->location(), $fh, 'public');
    }

    public function setFileContents(string $contents)
    {
        $this->deleteFile();
        $this->disk()->put($this->location(), $contents, 'public');
    }

    public function deleteFile(?ImageSize $size = null)
    {
        // Array of locations which we will delete
        $locations = [];
        if ($size) {
            // Size has been specified, so only delete this one size
            $locations[] = $this->location($size);
        } else {
            // No size specified - assume we're deleting all
            // Find all files with the right slug
            $base = $this->slug . '.';
            foreach ($this->disk()->files('lcf-media') as $file) {
                if (strpos($file, $base) === 0) {
                    $locations[] = "lcf-media/{$file}";
                }
            }
        }
        // Now we have a list of locaitons to delete, actually delete them on the disk
        foreach ($locations as $location) {
            if ($this->disk()->exists($location)) {
                $this->disk()->delete($location);
            }
        }
    }

    public function fileExists(?ImageSize $size = null)
    {
        return $this->disk()->exists($this->location($size));
    }

    public function getStream(?ImageSize $size = null)
    {
        return $this->disk()->readStream($this->location($size));
    }

    public function fileContents(?ImageSize $size = null)
    {
        return $this->disk()->get($this->location($size));
    }

    public function delete()
    {
        $this->deleteFile();
        parent::delete();
    }
}
