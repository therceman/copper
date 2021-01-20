<?php


namespace Copper\Test\DB;


use Copper\Traits\EntityStateFields;
use Copper\Component\Auth\AbstractUser;

class TestDBEntity extends AbstractUser
{
    use EntityStateFields;

    /** @var string */
    public $email;
    /** @var string */
    public $name;
    /** @var float */
    public $salary;
}