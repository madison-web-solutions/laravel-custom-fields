<?php
namespace MadisonSolutions\LCF\Group;

use JsonSerializable;

abstract class ObjectTest implements JsonSerializable
{
    abstract public function appliesTo(object $obj) : bool;

    public function jsonSerialize()
    {
        return [
            get_class($this),
        ];
    }
}
