<?php


namespace Copper\Component\DB;

class DBConfigurator
{
    /** @var string */
    public $host = '';
    /** @var string */
    public $dbname = '';
    /** @var string */
    public $user = '';
    /** @var string */
    public $password = '';

    /** @var string */
    public $hashSalt = '1337';
}