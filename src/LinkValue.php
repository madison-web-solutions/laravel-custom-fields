<?php
namespace MadisonSolutions\LCF;

class LinkValue
{
    protected $manual;
    protected $obj;
    protected $url;
    protected $default_label;
    protected $overriden_label;

    protected function __construct($manual, $obj, $url, $default_label, $overriden_label)
    {
        $this->manual = $manual;
        $this->obj = $obj;
        $this->url = $url;
        $this->default_label = $default_label;
        $this->overriden_label = $overriden_label;
    }

    public static function fromManual(string $url, ?string $label)
    {
        if (empty($url)) {
            return null;
        }
        return new LinkValue(true, null, $url, $url, $label);
    }

    public static function fromObjSpec(string $obj, ?string $label)
    {
        if (empty($obj)) {
            return null;
        }
        $info = app(LCF::class)->getLinkFinder()->lookup($obj);
        if ($info) {
            return new LinkValue(false, $obj, $info['url'], $info['label'], $label);
        } else {
            return new LinkValue(false, $obj, '', '', $label);
        }
    }

    public function __get($key)
    {
        switch ($key) {
            case 'manual':
                return $this->manual;
            case 'obj':
                return $this->obj;
            case 'url':
                return $this->url;
            case 'label':
                return empty($this->overriden_label) ? $this->default_label : $this->overriden_label;
        }
    }

    public function __isset($key)
    {
        switch ($key) {
            case 'manual':
            case 'obj':
            case 'url':
            case 'label':
                return true;
        }
    }

    public function toArray()
    {
        $out = [
            'manual' => $this->manual,
            'label' => $this->overriden_label,
        ];
        if ($this->manual) {
            $out['url'] = $this->url;
        } else {
            $out['obj'] = $this->obj;
        }
        return $out;
    }
}
