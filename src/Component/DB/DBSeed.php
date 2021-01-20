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
     */
    public function seed(AbstractEntity $entity)
    {
        /** @var DBModel $model */
        $model = new $this->modelClassName();

        $seedData = $model->getFieldValuesFromEntity($entity);
        $formattedSeedData = $model->formatFieldValues($seedData, false);

        $this->seeds[] = $formattedSeedData;
    }
}