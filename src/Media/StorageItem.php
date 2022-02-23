<?php

namespace MadisonSolutions\LCF\Media;

use Log;
use Storage;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Str;
use Intervention\Image\Exception\NotReadableException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class StorageItem
{
    protected $slug;
    protected $extension;
    protected $dir;
    protected $disk_name;

    public function __construct(string $slug, string $extension, string $dir, string $disk_name = 'public')
    {
        if (! preg_match('/^[-a-z0-9]+$/', $slug)) {
            throw new \Exception("Invalid slug {$slug}");
        }
        $this->slug = $slug;
        $this->extension = $extension;
        $this->dir = trim($dir, '/');
        $this->disk_name = $disk_name;
    }

    public function __get($name)
    {
        switch ($name) {
            case 'slug':
            case 'extension':
            case 'dir':
                return $this->$name;
        }
    }

    public function __isset($name)
    {
        switch ($name) {
            case 'slug':
            case 'extension':
            case 'dir':
                return true;
        }
        return false;
    }

    public function disk()
    {
        return Storage::disk($this->disk_name);
    }

    protected function fileName($size = null)
    {
        $size = (is_null($size) ? null : ImageSize::coerce($size));
        if ($size) {
            return $size->fileName($this->slug);
        } else {
            return "{$this->slug}.{$this->extension}";
        }
    }

    public function location($size = null)
    {
        return $this->dir . '/' . $this->fileName($size);
    }

    public function url($size = null)
    {
        return $this->disk()->url($this->location($size)) . '?t=' . ($this->fileExists() ? $this->lastModified() : '');
    }

    public function urlOrCreate($size = null)
    {
        if (config('lcf.automatically_create_webp_images', false) && $size) {
            $this->maybeCreateWebpVersion($size);
        }
        if (! $this->fileExists($size)) {
            if (! $this->createImageSize($size)) {
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
            if (! $this->fileExists($webp_size)) {
                $this->createImageSize($webp_size, true);
            }
        }
    }

    // throws Intervention\Image\Exception\NotReadableException
    public function createImageSize($size, bool $defer = false)
    {
        $size = ImageSize::coerce($size);
        if (! $this->fileExists(null)) {
            // The original file doesn't exist, so creating a resized version is bound to fail
            $orig_loc = $this->location(null);
            Log::warning("Failed to resize LCF media item - original file {$orig_loc} not found.");
            return false;
        }
        try {
            if ($defer) {
                CreateImageSizeJob::dispatch($this, $size);
            } else {
                CreateImageSizeJob::dispatchNow($this, $size);
            }
        } catch (NotReadableException $e) {
            Log::error("Intervention could not read image data for ".$this->location()." - resize failed");
            return false;
        }
        return true;
    }

    public function fileSize($size = null)
    {
        return $this->disk()->size($this->location($size));
    }

    public function setFileFromUpload(UploadedFile $upload)
    {
        if (! $upload->isValid()) {
            throw new \Exception("Uploaded file not valid");
        }
        $this->setFileByPath($upload->getPathname());
    }

    public function setFileByPath(string $srcPath)
    {
        $this->deleteFile();
        $fh = fopen($srcPath, 'rb');
        if (! $fh) {
            throw new \Exception("Failed to open file at path '{$srcPath}'");
        }
        $this->disk()->put($this->location(), $fh);
        fclose($fh);
    }

    public function setFileByResource($fh)
    {
        if (! is_resource($fh) || get_resource_type($fh) != 'stream') {
            throw new \InvalidArgumentException("Argument to setFileByResource() must be a stream resource");
        }
        $this->deleteFile();
        $this->disk()->put($this->location(), $fh);
    }

    public function setFileContents(string $contents)
    {
        $this->deleteFile();
        $this->disk()->put($this->location(), $contents);
    }

    public function getAllSizeLocations()
    {
        $locations = [];
        $driver = $this->disk()->getDriver();
        if ($driver instanceof \League\Flysystem\Filesystem) {
            $adaptor = $driver->getAdapter();
            if ($adaptor instanceof \League\Flysystem\Adapter\Local) {
                // Files are on the local disk so we can use a regular PHP DirectoryIterator iterator to save memory
                $dir_path = $adaptor->getPathPrefix() . $this->dir;
                $iterator = new \DirectoryIterator($dir_path);
                $base = "{$this->slug}.";
                foreach ($iterator as $file) {
                    if ($file->isFile()) {
                        if (strpos($file->getBasename(), $base) === 0) {
                            $locations[] = $this->dir . '/' . $file->getBasename();
                        }
                    }
                }
                return $locations;
            }
        }

        // Otherwise files are on some other kind of filesystem and we'll have to use Laravel's files() method
        // (and hope there aren't so many files that we run out of memory)
        $base = $this->dir . '/' . $this->slug . '.';
        foreach ($this->disk()->files($this->dir) as $file) {
            if (strpos($file, $base) === 0) {
                $locations[] = $file;
            }
        }

        return $locations;
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
            $locations = $this->getAllSizeLocations();
        }
        // Now we have a list of locations to delete, actually delete them on the disk
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

    public function lastModified($size = null)
    {
        return $this->disk()->lastModified($this->location($size));
    }

    public function getStream($size = null)
    {
        return $this->disk()->readStream($this->location($size));
    }

    public function fileContents($size = null)
    {
        return $this->disk()->get($this->location($size));
    }
}
