<?php


namespace Copper\Resource;


use Copper\Component\DB\DBModel;
use Copper\Component\DB\DBSeed;

abstract class AbstractRelationResource
{
    private static $model = null;

    abstract static function getModelClassName();

    static function getSeedClassName()
    {
        return DBSeed::class;
    }

    /**
     * @return DBSeed|string
     */
    static function getSeed()
    {
        return static::getSeedClassName();
    }

    /**
     * @return DBModel
     */
    static function getModel()
    {
        if (self::$model !== null)
            return self::$model;

        $className = static::getModelClassName();

        return self::$model = new $className();
    }

}