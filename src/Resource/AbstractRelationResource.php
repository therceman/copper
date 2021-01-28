<?php


namespace Copper\Resource;


abstract class AbstractRelationResource
{
    abstract static function getModelClassName();

    static function getSeedClassName()
    {
        // provide Seed Class Name if exists
    }

}