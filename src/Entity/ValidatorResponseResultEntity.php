<?php


namespace Copper\Entity;


class ValidatorResponseResultEntity
{
    /** @var string */
    public $msg;
    /** @var bool */
    public $status;
    /** @var string */
    public $orig_msg;
    /** @var mixed */
    public $result;

    public function __construct($orig_msg, $msg, $result, $status)
    {
        $this->msg = $msg;
        $this->orig_msg = $orig_msg;
        $this->result = $result;
        $this->status = $status;
    }
}