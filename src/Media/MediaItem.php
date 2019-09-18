<?php
namespace MadisonSolutions\LCF\Media;

use Log;
use Storage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\ImageManager;

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

    protected function fileName($size = null)
    {
        $size = (is_null($size) ? null : ImageSize::coerce($size));
        if (empty($this->slug)) {
            throw new \Exception("Must set MediaItem slug before accessing filename");
        }
        if ($size) {
            if (! $this->type->sizable) {
                throw new \Exception("Media of type {$this->type->label} cannot be resized");
            }
            return $size->fileName($this->slug);
        } else {
            return "{$this->slug}.{$this->extension}";
        }
    }

    public function location($size = null)
    {
        return 'lcf-media/' . $this->fileName($size);
    }

    public function url($size = null)
    {
        return $this->disk()->url($this->location($size));
    }

    public function urlOrCreate($size = null)
    {
        if (config('lcf.automatically_create_webp_images', false) && $size) {
            $this->maybeCreateWebpVersion($size);
        }
        if (! $this->fileExists($size)) {
            try {
                $this->createImageSize($size);
            } catch (NotReadableException $e) {
                Log::warning("urlOrCreate failed - Invalid image data for " . $this->location() . ": " . $e->getMessage());
                return false;
            } catch (FileNotFoundException $e) {
                Log::warning("urlOrCreate failed - Original image file not found for for " . $this->location());
                return false;
            }
        }
        return $this->url($size);
    }

    protected function maybeCreateWebpVersion($size)
    {
        $size = ImageSize::coerce($size);
        if ($size->format == 'png' || $size->format == 'jpg') {
            $webp_size = (clone $size)->setFormat('webp');
            $this->urlOrCreate($webp_size);
        }
    }

    // throws Intervention\Image\Exception\NotReadableException
    public function createImageSize($size)
    {
        $size = ImageSize::coerce($size);
        $this->deleteFile($size);
        $location = $this->location($size);
        $imageManager = new ImageManager(['driver' => 'imagick']);
        $image = $imageManager->make($this->fileContents());
        $this->disk()->put($this->location($size), $size->resize($image));
    }

    public function fileSize($size = null)
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

    public function deleteFile($size = null)
    {
        $size = (is_null($size) ? null : ImageSize::coerce($size));
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

    public function fileExists($size = null)
    {
        return $this->disk()->exists($this->location($size));
    }

    public function getStream($size = null)
    {
        return $this->disk()->readStream($this->location($size));
    }

    public function fileContents($size = null)
    {
        return $this->disk()->get($this->location($size));
    }

    public function delete()
    {
        $this->deleteFile();
        parent::delete();
    }
}
