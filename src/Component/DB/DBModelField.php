<?php


namespace Copper\Component\DB;


use Copper\Kernel;

class DBModelField
{
    // ============== TYPE ==============

    // ------- Numeric -------
    // Length for this fields only used for Zerofill Attribute. E.g. length = 4, value = 2  ===> 0002

    /** A 1-byte integer, signed range is -128 to 127, unsigned range is 0 to 255 */
    const TINYINT = 'TINYINT';

    /** A 2-byte integer, signed range is -32,768 to 32,767, unsigned range is 0 to 65,535 */
    const SMALLINT = 'SMALLINT';

    /** A 3-byte integer, signed range is -8,388,608 to 8,388,607, unsigned range is 0 to 16,777,215 */
    const MEDIUMINT = 'MEDIUMINT';

    /** A 4-byte integer, signed range is -2,147,483,648 to 2,147,483,647, unsigned range is 0 to 4,294,967,295 */
    const INT = 'INT';

    /** An 8-byte integer, signed range is -9,223,372,036,854,775,808 to 9,223,372,036,854,775,807, unsigned range is 0 to 18,446,744,073,709,551,615 */
    const BIGINT = 'BIGINT';

    /** A fixed-point number (M, D) - the maximum number of digits (M) is 65 (default 10), the maximum number of decimals (D) is 30 (default 0) */
    const DECIMAL = 'DECIMAL';

//    /** A small floating-point number, allowable values are -3.402823466E+38 to -1.175494351E-38, 0, and 1.175494351E-38 to 3.402823466E+38 */
//    const FLOAT = 'FLOAT';
//
//    /** A double-precision floating-point number, allowable values are -1.7976931348623157E+308 to -2.2250738585072014E-308, 0, and 2.2250738585072014E-308 to 1.7976931348623157E+308 */
//    const DOUBLE = 'DOUBLE';
//
//    /** Synonym for DOUBLE (exception: in REAL_AS_FLOAT SQL mode it is a synonym for FLOAT) */
//    const REAL = 'REAL';

//    /** A bit-field type (M), storing M of bits per value (default is 1, maximum is 64) */
//    const BIT = 'BIT';

    /** A synonym for TINYINT(1), a value of zero is considered false, nonzero values are considered true */
    const BOOLEAN = 'BOOLEAN';

//    /** An alias for BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE */
//    const SERIAL = 'SERIAL';

    // ------- Date and time -------

    /** A date, supported range is 1000-01-01 to 9999-12-31 */
    const DATE = 'DATE';

    /** A date and time combination, supported range is 1000-01-01 00:00:00 to 9999-12-31 23:59:59 */
    const DATETIME = 'DATETIME';

//    /** A timestamp, range is 1970-01-01 00:00:01 UTC to 2038-01-09 03:14:07 UTC, stored as the number of seconds since the epoch (1970-01-01 00:00:00 UTC) */
//    const TIMESTAMP = 'TIMESTAMP';

    /** A time, range is -838:59:59 to 838:59:59 */
    const TIME = 'TIME';

    /** A year in four-digit (4, default) the allowable values are 1901 to 2155 and 0000 */
    const YEAR = 'YEAR';

    // ------- String -------

//    /** A fixed-length (0-255, default 1) string that is always right-padded with spaces to the specified length when stored */
//    const CHAR = 'CHAR';

    /** A variable-length (0-65,535) string, the effective maximum length is subject to the maximum row size */
    const VARCHAR = 'VARCHAR';

    /** A TEXT column with a maximum length of 255 (2^8 - 1) characters, stored with a one-byte prefix indicating the length of the value in bytes */
    const TINYTEXT = 'TINYTEXT';

    /** A TEXT column with a maximum length of 65,535 (2^16 - 1) characters, stored with a two-byte prefix indicating the length of the value in bytes */
    const TEXT = 'TEXT';

    /** A TEXT column with a maximum length of 16,777,215 (2^24 - 1) characters, stored with a three-byte prefix indicating the length of the value in bytes */
    const MEDIUMTEXT = 'MEDIUMTEXT';

    /** A TEXT column with a maximum length of 4,294,967,295 or 4GiB (2^32 - 1) characters, stored with a four-byte prefix indicating the length of the value in bytes */
    const LONGTEXT = 'LONGTEXT';

//    /** Similar to the CHAR type, but stores binary byte strings rather than non-binary character strings */
//    const BINARY = 'BINARY';
//
//    /** Similar to the VARCHAR type, but stores binary byte strings rather than non-binary character strings */
//    const VARBINARY = 'VARBINARY';
//
//    /** A BLOB column with a maximum length of 255 (2^8 - 1) bytes, stored with a one-byte prefix indicating the length of the value */
//    const TINYBLOB = 'TINYBLOB';
//
//    /** A BLOB column with a maximum length of 65,535 (2^16 - 1) bytes, stored with a two-byte prefix indicating the length of the value */
//    const BLOB = 'BLOB';
//
//    /** A BLOB column with a maximum length of 16,777,215 (2^24 - 1) bytes, stored with a three-byte prefix indicating the length of the value */
//    const MEDIUMBLOB = 'MEDIUMBLOB';
//
//    /** A BLOB column with a maximum length of 4,294,967,295 or 4GiB (2^32 - 1) bytes, stored with a four-byte prefix indicating the length of the value */
//    const LONGBLOB = 'LONGBLOB';

    /** An enumeration, chosen from the list of up to 65,535 values or the special '' error value */
    const ENUM = 'ENUM';

//    /** A single value chosen from a set of up to 64 members */
//    const SET = 'SET';

//    // ------- Spatial -------
//
//    /** A type that can store a geometry of any type */
//    const GEOMETRY = 'GEOMETRY';
//
//    /** A point in 2-dimensional space */
//    const POINT = 'POINT';
//
//    /** A curve with linear interpolation between points */
//    const LINESTRING = 'LINESTRING';
//
//    /** A polygon */
//    const POLYGON = 'POLYGON';
//
//    /** A collection of points */
//    const MULTIPOINT = 'MULTIPOINT';
//
//    /** A collection of curves with linear interpolation between points */
//    const MULTILINESTRING = 'MULTILINESTRING';
//
//    /** A collection of polygons */
//    const MULTIPOLYGON = 'MULTIPOLYGON';
//
//    /** A collection of geometry objects of any type */
//    const GEOMETRYCOLLECTION = 'GEOMETRYCOLLECTION';
//
//    // ------- JSON -------
//
//    /** Stores and enables efficient access to data in JSON (JavaScript Object Notation) documents */
//    const JSON = 'JSON';

    // ============== Default ==============

    const DEFAULT_NONE = 'NONE';
    const DEFAULT_NULL = 'NULL';
    const DEFAULT_CURRENT_TIMESTAMP = 'CURRENT_TIMESTAMP';

    // ============== Attributes ==============

//    const ATTR_BINARY = 'BINARY';
//    /** Unsigned type can be used to permit only nonnegative numbers in a column or when you need a larger upper numeric range for the column. e.g. signed -127 to 127 , unsigned 0 to 255 */
    const ATTR_UNSIGNED = 'UNSIGNED';
//    /** Pads the displayed value of the field with zeros up to the display width specified in the column definition (Type), e.g. INT(8) will fill up to 7 zeros - 00000001 */
//    const ATTR_UNSIGNED_ZEROFILL = 'UNSIGNED ZEROFILL';
    /** Updates field value to current timestamp when on UPDATE */
    const ATTR_ON_UPDATE_CURRENT_TIMESTAMP = 'on update CURRENT_TIMESTAMP';

    // ============== Index ==============

    const INDEX_PRIMARY = 'PRIMARY';
    const INDEX_UNIQUE = 'UNIQUE';

    // ============== Fields ==============

    /** @var string Name */
    private $name;

    /** @var string Type - const [*] */
    private $type;

    /** @var bool|int|array (optional) Length or array of Values (if Type is ENUM) */
    private $length = false;

    /** @var string (optional) Default - value or const [DEFAULT_*] */
    private $default = self::DEFAULT_NONE;

    /** @var bool|string (optional) Attributes - const [ATTR_*] */
    private $attr = false;

    /** @var bool (optional) Null */
    private $null = false;

    /** @var bool|string (optional) Index - const [INDEX_*] */
    private $index = false;

    /** @var bool|string (optional) Index Name for Unique Index */
    private $indexName = false;

    /** @var bool (optional) Auto Increment - on INSERT when Type is *INT */
    private $autoIncrement = false;

    /**
     * DBModelField constructor.
     * @param string $name string Name
     * @param bool|string $type (optional) Type
     * @param bool|int|array $length (optional) Length
     */
    public function __construct(string $name, $type = false, $length = false)
    {
        $this->name = $name;

        if ($type !== false)
            $this->type = $type;

        if ($length !== false)
            $this->length = $length;
    }

    public static function cleanLength($length_list) {
        $list = [];

        foreach ($length_list as $val) {
            $list[] = trim($val);
        }

        return $list;
    }

    public static function fromArray($array)
    {
        $field = new DBModelField($array['name']);

        $length = $array['length'];

        if (is_string($length) && strpos($length, ',') !== false)
            $length = explode(',', $length);

        if (is_array($length))
            $length = self::cleanLength($length);

        $field->attr($array['attr']);
        $field->autoIncrement($array['auto_increment']);
        $field->default($array['default']);
        $field->index($array['index']);
        $field->length($length);
        $field->null($array['null']);
        $field->type($array['type']);

        return $field;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getLength()
    {
        $dbConfig = Kernel::getDb()->config;

        if ($this->type === self::DECIMAL && $this->length === false)
            return $dbConfig->default_decimal_length;

        if ($this->type === self::VARCHAR && $this->length === false)
            return $dbConfig->default_varchar_length;

        return $this->length;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function getAttr()
    {
        return $this->attr;
    }

    public function getNull()
    {
        return $this->null;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getIndexName()
    {
        return $this->indexName;
    }

    public function getAutoIncrement()
    {
        return $this->autoIncrement;
    }

    /**
     * @param string $type
     * @return DBModelField
     */
    public function type(string $type): DBModelField
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param bool|int|array $length
     *
     * @return DBModelField
     */
    public function length($length): DBModelField
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @param string|bool $default
     * @return DBModelField
     */
    public function default($default): DBModelField
    {
        $this->default = $default;

        if ($default === self::DEFAULT_NULL)
            $this->null = true;

        // Field with Auto Increment can't have default value
        if ($this->autoIncrement === true)
            $this->default = self::DEFAULT_NONE;

        return $this;
    }

    /**
     * @param string $attr
     * @return DBModelField
     */
    public function attr(string $attr): DBModelField
    {
        $this->attr = $attr;

        return $this;
    }

    /**
     * @param bool $null
     * @return DBModelField
     */
    public function null(bool $null = true): DBModelField
    {
        $this->null = $null;

        if ($this->null && $this->default === self::DEFAULT_NONE)
            $this->default = self::DEFAULT_NULL;

        return $this;
    }

    /**
     * @param string $index Index
     * @param bool $indexName (optional) Index Name for Unique Index
     * @return DBModelField
     */
    public function index(string $index, $indexName = false): DBModelField
    {
        $this->index = $index;

        if ($index === self::INDEX_PRIMARY && strtolower($this->name) === 'id' && strpos($this->type, 'INT') !== false)
            $this->autoIncrement(true);

        if ($indexName !== false)
            $this->indexName = $indexName;
        else
            $this->indexName = 'index_' . $this->name;

        return $this;
    }

    /**
     * @param bool $autoIncrement
     * @return DBModelField
     */
    public function autoIncrement(bool $autoIncrement = true): DBModelField
    {
        $this->autoIncrement = $autoIncrement;

        return $this;
    }

    // ----------------- Shortcuts -----------------

    /**
     * @param bool|string $indexName (optional) Index Name for Primary Index
     *
     * @return $this
     */
    public function primary($indexName = false)
    {
        $this->index(self::INDEX_PRIMARY, $indexName);

        return $this;
    }

    /**
     * @param bool|string $indexName (optional) Index Name for Unique Index
     *
     * @return $this
     */
    public function unique($indexName = false)
    {
        $this->index(self::INDEX_UNIQUE, $indexName);

        return $this;
    }

    /**
     * @return $this
     */
    public function nullByDefault()
    {
        $this->default(self::DEFAULT_NULL);

        return $this;
    }

    /**
     * @return $this
     */
    public function currentTimestampByDefault()
    {
        $this->default(self::DEFAULT_CURRENT_TIMESTAMP);

        return $this;
    }

//    /**
//     * @return $this
//     */
//    public function binary()
//    {
//        $this->attr(self::ATTR_BINARY);
//
//        return $this;
//    }

    /**
     * See description for ATTR_UNSIGNED
     *
     * @return $this
     */
    public function unsigned()
    {
        $this->attr(self::ATTR_UNSIGNED);

        return $this;
    }

//    /**
//     * See description for ATTR_UNSIGNED_ZEROFILL
//     *
//     * @return $this
//     */
//    public function unsignedZeroFill()
//    {
//        $this->attr(self::ATTR_UNSIGNED_ZEROFILL);
//
//        return $this;
//    }

    /**
     * See description for ATTR_ON_UPDATE_CURRENT_TIMESTAMP
     *
     * @return $this
     */
    public function currentTimestampOnUpdate()
    {
        $this->attr(self::ATTR_ON_UPDATE_CURRENT_TIMESTAMP);

        return $this;
    }

    // --------------------------------------

    public function typeIsInteger()
    {
        return (in_array($this->type, [
                DBModelField::INT,
                DBModelField::TINYINT,
                DBModelField::SMALLINT,
                DBModelField::MEDIUMINT,
                DBModelField::BIGINT,
//                DBModelField::SERIAL,
//                DBModelField::BIT
            ]) !== false);
    }

    public function typeIsString()
    {
        $isText = $this->typeIsText();
        $isVarchar = ($this->type === DBModelField::VARCHAR);

        return ($isText || $isVarchar);
    }

    public function typeIsText()
    {
        return (in_array($this->type, [
                DBModelField::TINYTEXT,
                DBModelField::TEXT,
                DBModelField::MEDIUMTEXT,
                DBModelField::LONGTEXT,
            ]) !== false);
    }

    public function typeIsFloat()
    {
        return (in_array($this->type, [
                DBModelField::DECIMAL,
//                DBModelField::FLOAT,
//                DBModelField::DOUBLE,
//                DBModelField::REAL
            ]) !== false);
    }

    public function typeIsBoolean()
    {
        return ($this->type === DBModelField::BOOLEAN);
    }

    public function typeIsEnum()
    {
        return ($this->type === DBModelField::ENUM);
    }

    public function typeIsDecimal()
    {
        return ($this->type === DBModelField::DECIMAL);
    }

    public function typeIsYear()
    {
        return ($this->type === DBModelField::YEAR);
    }

    public function typeIsTime()
    {
        return ($this->type === DBModelField::TIME);
    }

    public function typeIsDate()
    {
        return ($this->type === DBModelField::DATE);
    }

    public function typeIsDatetime()
    {
        return (in_array($this->type, [
                DBModelField::DATETIME,
//                DBModelField::TIMESTAMP,
            ]) !== false);
    }

    /**
     * Get Max Field Length by Type.
     *
     * Minus (-) sign is ignored for negative numbers.
     * Decimal number length is returned as array [maxDigits, maxDecimals]
     *
     * @return int|array
     */
    public function getMaxLength()
    {
        $dbConfig = Kernel::getDb()->config;

        $default_varchar_length = $dbConfig->default_varchar_length;
        $default_decimal_length = $dbConfig->default_decimal_length;

        $length = 0;

        if ($this->type === DBModelField::TINYINT)
            $length = 3;

        elseif ($this->type === DBModelField::SMALLINT)
            $length = 5;

        elseif ($this->type === DBModelField::MEDIUMINT)
            $length = ($this->attr === DBModelField::ATTR_UNSIGNED) ? 8 : 7;

        elseif ($this->type === DBModelField::INT)
            $length = 10;

        elseif ($this->type === DBModelField::BIGINT)
            $length = ($this->attr === DBModelField::ATTR_UNSIGNED) ? 20 : 19;

        elseif ($this->type === DBModelField::DECIMAL)
            $length = ($this->getLength() === false) ? $default_decimal_length : $this->getLength();

        elseif ($this->type === DBModelField::BOOLEAN)
            $length = 1;

        elseif ($this->type === DBModelField::DATE)
            $length = 10;

        elseif ($this->type === DBModelField::DATETIME)
            $length = 19;

        elseif ($this->type === DBModelField::TIME)
            $length = 9;

        elseif ($this->type === DBModelField::YEAR)
            $length = 4;

        elseif ($this->type === DBModelField::VARCHAR)
            $length = ($this->getLength() === false) ? $default_varchar_length : $this->getLength();

        elseif ($this->type === DBModelField::TINYTEXT)
            $length = 255;

        elseif ($this->type === DBModelField::TEXT)
            $length = 65535;

        elseif ($this->type === DBModelField::MEDIUMTEXT)
            $length = 16777215;

        elseif ($this->type === DBModelField::LONGTEXT)
            $length = 4294967295;

        elseif ($this->type === DBModelField::ENUM) {
            $longestValue = '';

            foreach ($this->length as $value) {
                if (strlen(strval($value)) > strlen($longestValue))
                    $longestValue = $value;
            }

            $length = strlen($longestValue);
        }

        return $length;
    }

}