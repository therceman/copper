<?php


namespace Copper\Test\DB;

use Copper\Component\DB\DBCollectionService;

class TestDBService extends DBCollectionService
{
    public static function getModelClassName()
    {
        return TestDBModel::class;
    }

    public static function getEntityClassName()
    {
        return TestDBEntity::class;
    }

}