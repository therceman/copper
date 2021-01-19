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

    /**
     * Check if status is true
     *
     * @return bool
     */
    public function isOK()
    {
        return ($this->status === true);
    }

    /**
     * Check if status is false
     *
     * @return bool
     */
    public function hasError()
    {
        return ($this->status === false);
    }

    /**
     * Sets the result. Status: true
     *
     * @param $result
     *
     * @return $this
     */
    public function result($result)
    {
        if ($result === false)
            return $this->error('Failed Result.');

        return $this->success("Success Result!", $result);
    }

    /**
     * Sets the result based on status.
     *
     * @param bool $status
     * @param mixed|false $result
     *
     * @return $this
     */
    public function okOrFail(bool $status, $result = false)
    {
        if ($status === false)
            return $this->error('Failed Result.', $result);

        return $this->success("Success Result!", $result);
    }

    /**
     * Alias for success. Status: true
     *
     * @param string|false $msg
     * @param mixed|false $result
     *
     * @return $this
     */
    public function ok($msg = false, $result = false)
    {
        return $this->success($msg, $result);
    }

    /**
     * Alias for error. Status: false
     *
     * @param string $msg
     * @param mixed $result
     *
     * @return $this
     */
    public function fail(string $msg, $result = false)
    {
        return $this->error($msg, $result);
    }

    /**
     * Success. Status: True
     *
     * @param string|false $msg
     * @param mixed|false $result
     *
     * @return $this
     */
    public function success($msg = false, $result = false)
    {
        $this->status = true;
        $this->msg = ($msg !== false) ? $msg : "success";

        if ($result !== false)
            $this->result = $result;

        return $this;
    }

    /**
     * Error. Status: False
     *
     * @param string $msg
     * @param mixed| false $result
     * @return $this
     */
    public function error(string $msg, $result = false)
    {
        $this->status = false;
        $this->msg = $msg;

        if ($result !== false)
            $this->result = $result;

        return $this;
    }

    /**
     * Check if result exists
     *
     * @return bool
     */
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