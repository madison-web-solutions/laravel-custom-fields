<?php
namespace MadisonSolutions\LCF;

use JsonSerializable;

class LinkValue implements JsonSerializable
{
    protected $manual;
    protected $link_id;
    protected $url;
    protected $default_label;
    protected $overriden_label;

    protected function __construct($manual, $link_id, $url, $default_label, $overriden_label)
    {
        $this->manual = $manual;
        $this->link_id = $link_id;
        $this->url = $url;
        $this->default_label = $default_label;
        $this->overriden_label = $overriden_label;
    }

    public static function fromManual(string $url, ?string $label) : ?LinkValue
    {
        if (empty($url)) {
            return null;
        }
        return new LinkValue(true, null, $url, $url, $label);
    }

    public static function fromLinkId(string $link_id, ?string $label) : ?LinkValue
    {
        if (empty($link_id)) {
            return null;
        }
        $info = app(LCF::class)->getLinkFinder()->lookup($link_id);
        if ($info) {
            return new LinkValue(false, $link_id, $info['url'], $info['label'], $label);
        } else {
            return new LinkValue(false, $link_id, '', '', $label);
        }
    }

    public function __get($key)
    {
        switch ($key) {
            case 'manual':
                return $this->manual;
            case 'link_id':
                return $this->link_id;
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
            case 'link_id':
            case 'url':
            case 'label':
                return true;
        }
    }

    public function jsonSerialize()
    {
        $out = [
            'manual' => $this->manual,
            'label' => $this->overriden_label,
        ];
        if ($this->manual) {
            $out['url'] = $this->url;
        } else {
            $out['link_id'] = $this->link_id;
        }
        return $out;
    }
}
