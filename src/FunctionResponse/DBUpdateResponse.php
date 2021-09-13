<?php


namespace Copper\FunctionResponse;


use Copper\Entity\AbstractEntity;
use Copper\FunctionResponse;

/**
 * Class DBUpdateResponse
 * @package Copper\FunctionResponse
 */
class DBUpdateResponse extends FunctionResponse
{
    /** @var DBUpdateResponseResultEntity */
    public $result;
}

/**
 * Class DBUpdateResponseEntity
 * @package Copper\FunctionResponse
 */
class DBUpdateResponseResultEntity extends AbstractEntity
{
    /** @var int */
    public $result_row_count;
    /** @var array */
    public $data;
    /** @var string */
    public $stmQuery;
    /** @var array */
    public $stmParam;

    /**
     * DBUpdateResponseEntity constructor.
     * @param $result_row_count
     * @param $data
     */
    public function __construct($result_row_count, $data, $stmQuery = null, $stmParam = null)
    {
        $this->result_row_count = $result_row_count;
        $this->data = $data;
        $this->stmQuery = $stmQuery;
        $this->stmParam = $stmParam;
    }
}