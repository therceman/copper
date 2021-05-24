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

    /**
     * DBUpdateResponseEntity constructor.
     * @param $result_row_count
     * @param $data
     */
    public function __construct($result_row_count, $data)
    {
        $this->result_row_count = $result_row_count;
        $this->data = $data;
    }
}