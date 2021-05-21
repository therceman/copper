<?php


namespace Copper\Component\DB;


use Copper\Entity\AbstractEntity;

abstract class DBSeed
{
    /** @var string */
    public $modelClassName = '';

    /** @var array */
    public $seeds = [];

    /** @var \Closure|null */
    private $onCompleteClosure = null;

    abstract function getModelClassName();

    abstract function setSeeds();

    public function __construct()
    {
        $this->modelClassName = $this->getModelClassName();
        $this->setSeeds();
    }

    /**
     * @param \Closure|null $closure
     */
    public function onComplete(\Closure $closure)
    {
        $this->onCompleteClosure = $closure;
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
        $formattedSeedData = $model->formatFieldValues($seedData, false, true);

        $this->seeds[] = $formattedSeedData;

        return count($this->seeds);
    }

    /**
     * @return \Closure|null
     */
    public function getOnCompleteClosure()
    {
        return $this->onCompleteClosure;
    }
}