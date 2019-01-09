<?php
namespace MadisonSolutions\LCF\Media;

use Intervention\Image\Image;

class ImageSize
{
    public static function coerce($value)
    {
        if ($value instanceof ImageSize) {
            return $value;
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
                if ($this->method == 'fit' || $this->method == 'widen') {
                    return $this->width;
                } else {
                    return 'auto';
                }
            case 'height':
                if ($this->method == 'fit' || $this->method == 'heighten') {
                    return $this->height;
                } else {
                    return 'auto';
                }
        }
    }

    public function setMethod(string $method)
    {
        $methods = ['none', 'fit', 'heighten', 'widen'];
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
        $formats = ['jpg', 'png'];
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
                $resized = $image->fit($this->width, $this->height, null, $this->position);
                break;
            case 'heighten':
                $resized = $image->heighten($this->height);
                break;
            case 'widen':
                $resized = $image->widen($this->width);
                break;
            case 'none':
                $resized = $image;
                break;
        }

        switch ($this->format) {
            case 'png':
                return $resized->encode('png');
                break;
            case 'jpg':
                return $resized->encode('jpg', 80);
                break;
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
            case 'none':
                $parts[] = "none";
                break;
        }
        $parts[] = $this->format;
        return implode('.', $parts);
    }
}
