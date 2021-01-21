<?php


namespace Copper\Component\DB;


class DBOrder
{
    const ASC = 'ASC';
    const DESC = 'DESC';

    /** @var array */
    private $fields;

    /**
     * DBCondition constructor.
     *
     * @param string $field
     * @param bool $orderByASC
     */
    public function __construct(string $field, $orderByASC = true)
    {
        $this->fields = [];

        $this->addField($field, $orderByASC);
    }

    private function addField($field, $orderByASC)
    {
        $this->fields[] = [$field, $orderByASC];
    }

    public function toString()
    {
        $queryList = [];

        foreach ($this->fields as $key => $data) {
            $order = ($data[1]) ? self::ASC : self::DESC;
            $queryList[] = DBModel::formatFieldName($data[0]) . ' ' . $order;
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

    public static function DESC(string $field)
    {
        return new self($field, false);
    }

    public static function ASC(string $field)
    {
        return new self($field, true);
    }

}