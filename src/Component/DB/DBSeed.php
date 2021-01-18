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
        $entityFields = $entity->toArray();

        /** @var DBModel $model */
        $model = new $this->modelClassName();

        $seedData = [];
        foreach ($model->fields as $field) {
            if (array_key_exists($field->name, $entityFields) === false)
                return;

            $value = $entityFields[$field->name];

            if (is_string($value))
                $value = "'$value'";

            if ($value === null && in_array($field->type, [$field::DATETIME, $field::DATE]) && $field->null !== true)
                $value = 'now()';

            if ($value === null && $field->type === $field::YEAR && $field->null !== true)
                $value = 'YEAR(CURDATE())';

            if ($value === null)
                $value = 'NULL';

            $seedData[] = $value;
        }

        $this->seeds[] = $seedData;
    }
}