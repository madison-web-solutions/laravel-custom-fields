<?php

namespace MadisonSolutions\LCF;

use JsonSerializable;

class SwitchValue implements JsonSerializable
{
    protected $switch;
    protected $value;

    public function __construct(string $switch, $value = null)
    {
        $this->switch = $switch;
        $this->value = $value;
    }

    public function __get($key)
    {
        switch ($key) {
            case 'switch':
                return $this->switch;
            case 'value':
                return $this->value;
        }
        if ($key === $this->switch) {
            return $this->value;
        }
    }

    public function __isset($key)
    {
        switch ($key) {
            case 'switch':
            case 'value':
                return true;
            default:
                return ($key === $this->switch);
        }
    }

    public function jsonSerialize()
    {
        $ret = [
            'switch' => $this->switch,
        ];
        $ret[$this->switch] = $this->value;
        return $ret;
    }
}
