<?php


namespace Copper\Entity;


class FunctionResponse
{
    /** @var bool */
    public $status;
    /** @var string */
    public $msg;
    /** @var mixed */
    public $result;

    /** @var bool */
    public $result_is_array;

    public function __construct($result_is_array = false)
    {
        $this->status = false;
        $this->msg = '';
        $this->result = ($result_is_array === true) ? [] : null;
        $this->result_is_array = $result_is_array;
    }

    public function isOK()
    {
        return ($this->status === true);
    }

    public function hasError()
    {
        return ($this->status === false);
    }

    public function success($msg = false, $result = false)
    {
        $this->status = true;
        $this->msg = ($msg !== false) ? $msg : "success";

        if ($result !== false)
            $this->result = $result;

        return $this;
    }

    public function error($msg, $result = false)
    {
        $this->status = false;
        $this->msg = $msg;

        if ($result !== false)
            $this->result = $result;

        return $this;
    }

    public function hasResult()
    {
        if ($this->result === null)
            return false;

        if (trim($this->result) === '')
            return false;

        if (is_array($this->result) && count($this->result) === 0)
            return false;

        return true;
    }
}