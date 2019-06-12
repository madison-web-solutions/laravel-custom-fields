<?php

namespace MadisonSolutions\LCF\Media;

use InvalidArgumentException;
use Intervention\Image\Image;

class ImageSize
{
    protected static $named_sizes;

    public static function register(string $name, ImageSize $size)
    {
        ImageSize::$named_sizes[$name] = $size;
    }

    public static function getNamedSizes()
    {
        return ImageSize::$named_sizes;
    }

    public static function coerce($value)
    {
        if ($value instanceof ImageSize) {
            return $value;
        }
        if (is_string($value)) {
            $size = ImageSize::$named_sizes[$value] ?? null;
            if (! $size) {
                throw new InvalidArgumentException("No registered ImageSize called '{$value}'");
            }
            return $size;
        }
        if (is_array($value)) {
            return new ImageSize($value);
        }
        throw new InvalidArgumentException();
    }

    protected $method;
    protected $width;
    protected $height;
    protected $position;
    protected $format;

    public function __construct(array $spec)
    {
        $this->setMethod($spec['method'] ?? 'none');
        $this->setWidth($spec['width'] ?? 200);
        $this->setHeight($spec['height'] ?? 200);
        $this->setPosition($spec['position'] ?? 'center');
        $this->setFormat($spec['format'] ?? 'jpg');
    }

    public function __get($name)
    {
        switch ($name) {
            case 'method':
            case 'position':
            case 'format':
                return $this->$name;
            case 'width':
                if (in_array($this->method, ['fit', 'widen', 'contain'])) {
                    return $this->width;
                } else {
                    return 'auto';
                }
            case 'height':
                if (in_array($this->method, ['fit', 'heighten', 'contain'])) {
                    return $this->height;
                } else {
                    return 'auto';
                }
        }
    }

    public function setMethod(string $method)
    {
        $methods = ['none', 'fit', 'heighten', 'widen', 'contain'];
        if (! in_array($method, $methods)) {
            throw new \Exception("Unexpected resize method '{$method}' - expected " . implode(', ', $methods));
        }
        $this->method = $method;
        return $this;
    }

    public function setWidth(int $width)
    {
        $this->width = $width;
        return $this;
    }

    public function setHeight(int $height)
    {
        $this->height = $height;
        return $this;
    }

    public function setPosition(string $position)
    {
        $positions = ['top-left', 'top', 'top-right', 'left', 'center', 'right', 'bottom-left', 'bottom', 'bottom-right'];
        if (! in_array($position, $positions)) {
            throw new \Exception("Unexpected position '{$position}' - expected " . implode(', ', $positions));
        }
        $this->position = $position;
        return $this;
    }

    public function setFormat(string $format)
    {
        $formats = ['jpg', 'png', 'webp'];
        if (! in_array($format, $formats)) {
            throw new \Exception("Unexpected format '{$format}' - expected " . implode(', ', $formats));
        }
        $this->format = $format;
        return $this;
    }

    public function resize(Image $image)
    {
        switch ($this->method) {
            case 'fit':
                return $image->fit($this->width, $this->height, null, $this->position);
            case 'heighten':
                return $image->heighten($this->height);
            case 'widen':
                return $image->widen($this->width);
            case 'contain':
                return ($image->height() > $image->width() ? $image->heighten($this->height) : $image->widen($this->width));
            case 'none':
                return $image;
        }
    }

    public function encode(Image $image)
    {
        switch ($this->format) {
            case 'png':
                return $image->encode('png');
            case 'jpg':
                return $image->encode('jpg', 80);
            case 'webp':
                // I couldn't get webp support working with either GD or ImageMagick
                // so I'm resorting to saving as png to a temporary file, then converting
                // to webp format via the cwebp command line tool
                if (! `which cwebp`) {
                    throw new \Exception("The cwebp command line tool must be installed in order to save images in webp format");
                }
                $temp_png_path = tempnam(sys_get_temp_dir(), 'png_');
                file_put_contents($temp_png_path, $image->encode('png'));
                $temp_webp_path = tempnam(sys_get_temp_dir(), 'web_p');
                exec('cwebp  ' . escapeshellarg($temp_png_path) . ' -o ' . escapeshellarg($temp_webp_path) . ' -quiet');
                $webp_image_data = file_get_contents($temp_webp_path);
                unlink($temp_png_path);
                unlink($temp_webp_path);
                return $webp_image_data;
        }
    }

    public function fileName(string $slug)
    {
        $parts = [$slug];
        switch ($this->method) {
            case 'fit':
                $parts[] = "fit-w{$this->width}-h{$this->height}-{$this->position}";
                break;
            case 'heighten':
                $parts[] = "h{$this->height}";
                break;
            case 'widen':
                $parts[] = "w{$this->height}";
                break;
            case 'contain':
                $parts[] = "contain-w{$this->width}-h{$this->height}";
                break;
            case 'none':
                $parts[] = "none";
                break;
        }
        $parts[] = $this->format;
        return implode('.', $parts);
    }
}
