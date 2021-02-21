<?php


namespace Copper\Component\DB;


class DBOrder
{
    const ASC = 'ASC';
    const DESC = 'DESC';

    /** @var array */
    private $fields;
    /** @var DBModel */
    private $model;

    /**
     * DBOrder constructor.
     *
     * @param string $field
     * @param DBModel $model
     * @param bool $orderByASC
     */
    public function __construct(DBModel $model, string $field, $orderByASC = true)
    {
        $this->fields = [];
        $this->model = $model;
        $this->addField($field, $orderByASC);
    }

    private function addField($field, $orderByASC)
    {
        $response = $this->model->hasFields([$field]);

        if ($response->isOK())
            $this->fields[] = [$field, $orderByASC];
    }

    public function toString()
    {
        $queryList = [];

        foreach ($this->fields as $key => $data) {
            $order = ($data[1]) ? self::ASC : self::DESC;
            $queryList[] = '`' . DBModel::formatFieldName($data[0]) . '` ' . $order;
        }

        return join(', ', $queryList);
    }

    public function andDESC(string $field)
    {
        $this->addField($field, false);

        return $this;
    }

    public function andASC(string $field)
    {
        $this->addField($field, true);

        return $this;
    }

    public function __toString()
    {
        return $this->toString();
    }

    public static function DESC(DBModel $model, string $field)
    {
        return new self($model, $field, false);
    }

    public static function ASC(DBModel $model, string $field)
    {
        return new self($model, $field, true);
    }

}