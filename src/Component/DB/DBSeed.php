<?php


namespace Copper\Component\DB;


use Copper\Entity\AbstractEntity;

abstract class DBSeed
{
    /** @var string */
    public $modelClassName = '';

    /** @var array */
    public $seeds = [];

    abstract function getModelClassName();

    abstract function setSeeds();

    public function __construct()
    {
        $this->modelClassName = $this->getModelClassName();
        $this->setSeeds();
    }

    /**
     * Add Seed
     * @param AbstractEntity $entity
     *
     * @return int - Inserted ID
     */
    public function seed(AbstractEntity $entity)
    {
        /** @var DBModel $model */
        $model = new $this->modelClassName();

        $seedData = $model->getFieldValuesFromEntity($entity);
        $formattedSeedData = $model->formatFieldValues($seedData, false);

        $this->seeds[] = $formattedSeedData;

        return count($this->seeds);
    }
}