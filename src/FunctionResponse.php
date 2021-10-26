<?php


namespace Copper;


use Copper\Entity\AbstractEntity;
use Copper\Handler\StringHandler;
use Copper\Handler\VarHandler;

class FunctionResponse
{
    /** @var bool */
    public $status;
    /** @var string */
    public $msg;
    /** @var int */
    public $code;
    /** @var mixed */
    public $result;

    public function __construct($status = true, $msg = '', $result = null, $code = 0)
    {
        $this->status = $status;
        $this->msg = $msg;
        $this->result = $result;
        $this->code = $code;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setCode(int $code): FunctionResponse
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Sets the result. Status: true when $result is not false (else throws error)
     *
     * @param $result
     * @param $falseResultMsg
     *
     * @return static
     */
    public static function createResult($result, $falseResultMsg = false)
    {
        $response = new FunctionResponse();

        if ($falseResultMsg === false)
            return $response->result($result);
        else
            return $response->result($result, $falseResultMsg);
    }

    /**
     * Error. Status: False
     *
     * @param $msg
     * @param mixed|false $result
     * @param int $code
     *
     * @return FunctionResponse
     */
    public static function createError($msg, $result = false, $code = 0)
    {
        $response = new FunctionResponse();

        return $response->error($msg, $result, $code);
    }

    /**
     * Success. Status: True
     *
     * @param $msg
     * @param false $result
     *
     * @return FunctionResponse
     */
    public static function createSuccess($msg = false, $result = false)
    {
        $response = new FunctionResponse();

        return $response->success($msg, $result);
    }

    /**
     * Sets the result based on status.
     *
     * @param bool $status
     * @param mixed $result
     *
     * @return FunctionResponse
     */
    public static function createSuccessOrError(bool $status, $result = false)
    {
        $response = new FunctionResponse();

        return $response->successOrError($status, $result);
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

        if ($this->hasError())
            return $this->error($this->msg, $result);

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
    public function successOrError(bool $status, $result = false)
    {
        if ($status !== true)
            return $this->error('Failed Result.', $result);

        return $this->success("Success Result!", $result);
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
     * @param int $code
     * @return $this
     */
    public function error(string $msg, $result = false, $code = 0)
    {
        $this->status = false;
        $this->msg = $msg;
        $this->code = $code;

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
        if (VarHandler::isNull($this->result))
            return false;

        if (StringHandler::isEmpty($this->result))
            return false;

        if (VarHandler::isArray($this->result) && count($this->result) === 0)
            return false;

        return true;
    }

    /**
     * @param string $entityClass
     * @return mixed
     */
    public function getResult(string $entityClass = AbstractEntity::class)
    {
        return $this->result;
    }

    // ------------------ Aliases ------------------

    /**
     * Alias for successOrError. Sets the result based on status.
     *
     * @param bool $status
     * @param mixed|false $result
     *
     * @return $this
     */
    public function okOrFail(bool $status, $result = false): FunctionResponse
    {
        return $this->successOrError($status, $result);
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
     * @param int $code
     *
     * @return $this
     */
    public function fail(string $msg, $result = false, $code = 0)
    {
        return $this->error($msg, $result, $code);
    }
}