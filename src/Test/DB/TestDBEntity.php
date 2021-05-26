<?php


namespace Copper\Test\DB;


use Copper\Traits\EntityStateFields;
use Copper\Component\Auth\AbstractUserEntity;

class TestDBEntity extends AbstractUserEntity
{
    use EntityStateFields;

    /** @var string */
    public $email;
    /** @var string */
    public $name;
    /** @var string */
    public $is;
    /** @var float */
    public $salary;
    /** @var string */
    public $enum;
    /** @var float */
    public $dec_def;
    /** @var int */
    public $int;
}