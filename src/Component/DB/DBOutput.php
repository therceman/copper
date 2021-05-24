<?php


namespace Copper\Component\DB;


class DBOutput
{
    /** @var \Closure|null */
    private $map;
    /** @var array|null */
    private $fields;
    /** @var array|null */
    private $deletedFields;
    /** @var string|null */
    private $index;
    /** @var string|null */
    private $listOutputField;

    public function __construct()
    {
        $this->map = null;
        $this->fields = null;
        $this->deletedFields = null;
        $this->index = null;
        $this->listOutputField = null;
    }

    /**
     * @param string|null $field
     *
     * @return DBOutput
     */
    public static function listOutput($field)
    {
        $self = new self();

        return $self->setListOutput($field);
    }

    /**
     * @param \Closure|null $mapClosure
     *
     * @return DBOutput
     */
    public static function map(\Closure $mapClosure)
    {
        $self = new self();

        return $self->setMap($mapClosure);
    }

    /**
     * @param string|string[]|null $fields
     *
     * @return DBOutput
     */
    public static function fields($fields)
    {
        $self = new self();

        return $self->setFields($fields);
    }

    /**
     * @param string|string[]|null $fields
     *
     * @return DBOutput
     */
    public static function deleteFields($fields)
    {
        $self = new self();

        return $self->setDeletedFields($fields);
    }

    /**
     * Returned list keys will be replaced with provided field value
     * <hr>
     * <code>
     * - index('name')
     * # array(
     * #   'john' => ['id' => 1, 'name' => 'john'],
     * #   'bob'  => ['id' => 2, 'name' => 'bob']
     * # )
     *
     * </code>
     *
     * @param string|null $index - Entity field name for index
     *
     * @return DBOutput
     */
    public static function index($index = DBModel::ID)
    {
        $self = new self();

        return $self->setIndex($index);
    }

    /**
     * Set Entity map
     * <hr>
     * <code>
     * setMap(function($entity) {
     *  return $entity->set('demo','123);
     * });
     * </code>
     * @param \Closure|null $mapClosure
     *
     * @return $this
     */
    public function setMap(\Closure $mapClosure)
    {
        $this->map = $mapClosure;

        return $this;
    }

    /**
     * @param string|string[]|null $fields
     *
     * @return DBOutput
     */
    public function setFields($fields): DBOutput
    {
        $this->fields = is_array($fields) ? $fields : [$fields];

        return $this;
    }

    /**
     * @param string|string[]|null $fields
     *
     * @return DBOutput
     */
    public function setDeletedFields($fields): DBOutput
    {
        $this->deletedFields = is_array($fields) ? $fields : [$fields];

        return $this;
    }

    /**
     * @param string|null $index
     * @return DBOutput
     */
    public function setIndex($index = DBModel::ID)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * @param string|null $field
     * @return DBOutput
     */
    public function setListOutput($field): DBOutput
    {
        $this->listOutputField = $field;

        return $this;
    }

    /**
     * @return \Closure|null
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @return array|null
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return array|null
     */
    public function getDeletedFields()
    {
        return $this->deletedFields;
    }

    /**
     * @return string|null
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return string|null
     */
    public function getListOutputField()
    {
        return $this->listOutputField;
    }

}