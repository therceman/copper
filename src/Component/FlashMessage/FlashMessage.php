<?php

namespace Copper\Component\FlashMessage;

class FlashMessage
{
    const ERROR = 'error';
    const SUCCESS = 'success';
    const WARNING = 'warning';
    const INFO = 'info';
    const INPUT_LIST = 'encoded_input_list';
    const VALIDATION_ERROR_LIST = 'encoded_validation_list';

    /** @var string */
    public $type;
    /** @var string */
    public $text;

    /**
     * SessionMessage constructor.
     *
     * @param string $type
     * @param string $text
     */
    public function __construct(string $type, string $text)
    {
        $this->text = $text;
        $this->type = $type;
    }
}