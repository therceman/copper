<?php


namespace Copper\FunctionResponse;


use Copper\Entity\ValidatorResponseResultEntity;
use Copper\FunctionResponse;
use Copper\Handler\ArrayHandler;

/**
 * Class ValidatorResponse
 * @package Copper\FunctionResponse
 */
class ValidatorResponse extends FunctionResponse
{
    /** @var ValidatorResponseResultEntity[] */
    public $result;

    public function origMsg($key)
    {
        if (ArrayHandler::hasKey($this->result, $key))
            return $this->result[$key]->orig_msg;

        return null;
    }

    public function msg($key)
    {
        if (ArrayHandler::hasKey($this->result, $key))
            return $this->result[$key]->msg;

        return null;
    }

    public function overrideMsg($key, $origMsg, $newMsg)
    {
        if (ArrayHandler::hasKey($this->result, $key) && $this->result[$key]->orig_msg === $origMsg) {
            $this->result[$key]->msg = $newMsg;
            return true;
        }

        return false;
    }
}

