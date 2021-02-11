<?php


namespace Copper;


class FunctionResponse
{
    /** @var bool */
    public $status;
    /** @var string */
    public $msg;
    /** @var mixed */
    public $result;

    public function __construct($status = false, $msg = '', $result = null)
    {
        $this->status = $status;
        $this->msg = $msg;
        $this->result = $result;
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
     * Sets the result. Status: true when $result is not false (else throws error)
     *
     * @param mixed $result
     * @param string $falseResultMsg
     *
     * @return $this
     */
    public function result($result, string $falseResultMsg = 'Failed Result.')
    {
        if ($result === false)
            return $this->error($falseResultMsg);

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