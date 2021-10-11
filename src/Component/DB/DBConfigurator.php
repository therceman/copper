<?php


namespace Copper\Component\DB;

class DBConfigurator
{
    /** @var bool */
    public $enabled;
    
    /** @var bool */
    public $debug;

    /** @var bool */
    public $log_errors;
    /** @var string */
    public $log_prefix;

    /** @var string */
    public $host;
    /** @var string */
    public $dbname;
    /** @var string */
    public $user;
    /** @var string */
    public $password;

    /** @var string */
    public $engine;

    /** @var string */
    public $hashSalt;

    /** @var int */
    public $default_varchar_length;

    /** @var int[] */
    public $default_decimal_length;

    /** @var bool Trim VARCHAR on update or create */
    public $trim_varchar;

    /** @var bool Trim TEXT on update or create */
    public $trim_text;

    /** @var bool Trim ENUM on update or create */
    public $trim_enum;

    /**
     * Boolean field is not strict on update or create.
     * <hr>
     * <code>
     * You can pass the following values to boolean field
     * - true - true
     * - false - false
     * - '1' - true
     * - '0' - false
     * - 1 - true
     * - 0 - false
     * - 'true' - true
     * - 'false' - false
     * - '' - false
     * - null - false
     * </code>
     * @var bool
     */
    public $boolean_not_strict;

    /**
     * Throw error on: No record found for update or new data not provided
     * @var bool
     */
    public $throwErrorWhenNothingToUpdate;

    /**
     * Needed for DBColumnMod
     * @var int|float
     */
    public $ifNullDefaultValue;
}