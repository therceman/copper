<?php


namespace Copper\Component\DB;


class DBMatchColumnModValue
{
    public $required;
    public $anyEnd;
    public $anyStart;
    public $value;

    public function __construct($value, $required = false)
    {
        $this->value = $value;
        $this->required = $required;
        $this->anyEnd = false;
        $this->anyStart = false;
    }

    public static function new($value, $required = false)
    {
        return new self($value, $required);
    }

    public function required($bool = true)
    {
        $this->required = $bool;
        return $this;
    }

    public function anyEnd($bool = true)
    {
        $this->anyEnd = $bool;
        return $this;
    }

    public function anyStart($bool = true)
    {
        $this->anyStart = $bool;
        return $this;
    }
}