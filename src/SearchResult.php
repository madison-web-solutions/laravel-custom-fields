<?php

namespace MadisonSolutions\LCF;

use JsonSerializable;

class SearchResult implements JsonSerializable
{
    protected $id;
    protected $display_name;

    public function __construct(string $id, string $display_name)
    {
        $this->id = $id;
        $this->display_name = $display_name;
    }

    public function __get($key)
    {
        switch ($key) {
            case 'id':
            case 'display_name':
                return $this->$key;
        }
    }

    public function __isset($key)
    {
        switch ($key) {
            case 'id':
            case 'display_name':
                return true;
        }
        return false;
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'display_name' => $this->display_name,
        ];
    }
}
