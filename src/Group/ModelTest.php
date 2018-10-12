<?php
namespace MadisonSolutions\LCF\Group;

use Illuminate\Database\Eloquent\Model;

class ModelTest extends ObjectTest
{
    protected $modelname;

    public function __construct(?string $modelname = null)
    {
        $this->modelname = $modelname;
    }

    public function jsonSerialize()
    {
        return [
            get_class($this),
            $this->modelname,
        ];
    }

    public function appliesTo(object $obj) : bool
    {
        $applies = ($obj instanceof Model);
        if ($this->modelname) {
            $applies = $applies && (snake_case(class_basename($obj)) === $this->modelname);
        }
        return $applies;
    }
}
