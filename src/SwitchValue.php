<?php
namespace MadisonSolutions\LCF;

class SwitchValue
{
    protected $switch;
    protected $value;

    public function __construct(string $switch, $value)
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
}
