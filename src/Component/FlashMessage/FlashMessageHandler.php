<?php

namespace Copper\Component\FlashMessage;

use Symfony\Component\HttpFoundation\Session\Session;

class FlashMessageHandler
{
    /** @var Session */
    private $session;

    /**
     * FlashMessageHandler constructor.
     *
     * @param Session $session
     */
    public function __construct(Session &$session)
    {
        $this->session = $session;
    }

    /**
     * Set message text by type
     *
     * @param string $type
     * @param string $text
     */
    public function set(string $type, string $text)
    {
        $this->session->getFlashBag()->set($type, $text);
    }

    /**
     * @param string $text
     */
    public function setError(string $text)
    {
        $this->session->getFlashBag()->set(FlashMessage::ERROR, $text);
    }

    /**
     * @param string $text
     */
    public function setSuccess(string $text)
    {
        $this->session->getFlashBag()->set(FlashMessage::SUCCESS, $text);
    }

    /**
     * @param string $text
     */
    public function setInfo(string $text)
    {
        $this->session->getFlashBag()->set(FlashMessage::INFO, $text);
    }

    /**
     * @param string $text
     */
    public function setWarning(string $text)
    {
        $this->session->getFlashBag()->set(FlashMessage::WARNING, $text);
    }

    /**
     * Set message text by type
     *
     * @param string $type
     * @param string $text
     */
    public function add(string $type, string $text)
    {
        $this->session->getFlashBag()->add($type, $text);
    }

    /**
     * @param string $text
     */
    public function addError(string $text)
    {
        $this->session->getFlashBag()->add(FlashMessage::ERROR, $text);
    }

    /**
     * @param string $text
     */
    public function addSuccess(string $text)
    {
        $this->session->getFlashBag()->add(FlashMessage::SUCCESS, $text);
    }

    /**
     * @param string $text
     */
    public function addInfo(string $text)
    {
        $this->session->getFlashBag()->add(FlashMessage::INFO, $text);
    }

    /**
     * @param string $text
     */
    public function addWarning(string $text)
    {
        $this->session->getFlashBag()->add(FlashMessage::WARNING, $text);
    }

    /**
     * Get first message text by type
     *
     * @param string $type
     *
     * @param bool $default
     * @return string|false
     */
    public function get(string $type, $default = false)
    {
        $entries = $this->session->getFlashBag()->get($type);

        return (count($entries) > 0) ? $entries[0] : $default;
    }

    /**
     * Get first error message
     *
     * @param bool $default
     * @return FlashMessage|false
     */
    public function getError($default = false)
    {
        return $this->get(FlashMessage::ERROR, $default);
    }

    /**
     * Get first success message
     *
     * @param bool $default
     * @return FlashMessage|false
     */
    public function getSuccess($default = false)
    {
        return $this->get(FlashMessage::SUCCESS, $default);
    }

    /**
     * Get first info message
     *
     * @param bool $default
     * @return FlashMessage|false
     */
    public function getInfo($default = false)
    {
        return $this->get(FlashMessage::INFO, $default);
    }

    /**
     * Get first warning message
     *
     * @param bool $default
     * @return FlashMessage|false
     */
    public function getWarning($default = false)
    {
        return $this->get(FlashMessage::WARNING, $default);
    }

    /**
     * Returns true if the message with type exists, false if not.
     *
     * @param string $type
     *
     * @return bool
     */
    public function has(string $type)
    {
        return $this->session->getFlashBag()->has($type);
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return $this->session->getFlashBag()->has(FlashMessage::ERROR);
    }

    /**
     * @return bool
     */
    public function hasSuccess()
    {
        return $this->session->getFlashBag()->has(FlashMessage::SUCCESS);
    }

    /**
     * @return bool
     */
    public function hasInfo()
    {
        return $this->session->getFlashBag()->has(FlashMessage::INFO);
    }

    /**
     * @return bool
     */
    public function hasWarning()
    {
        return $this->session->getFlashBag()->has(FlashMessage::WARNING);
    }

    /**
     * Returns the list of message texts by type
     *
     * @param string $type
     * @return string[]
     */
    public function list(string $type)
    {
        $flashMessageList = [];

        foreach ($this->session->getFlashBag()->get($type, []) as $msg) {
            $flashMessageList[] = $msg;
        }

        return $flashMessageList;
    }

    /**
     * Returns true if message type has multiple records
     *
     * @param string $type
     * @return bool
     */
    public function isList(string $type)
    {
        $entries = $this->session->getFlashBag()->get($type);

        return count($entries) > 0;
    }

    /**
     *  Returns the list of error messages
     *
     * @return string[]
     */
    public function listError()
    {
        return $this->list(FlashMessage::ERROR);
    }

    /**
     *  Returns the list of success messages
     *
     * @return string[]
     */
    public function listSuccess()
    {
        return $this->list(FlashMessage::SUCCESS);
    }

    /**
     *  Returns the list of warning messages
     *
     * @return string[]
     */
    public function listWarning()
    {
        return $this->list(FlashMessage::WARNING);
    }

    /**
     *  Returns the list of info messages
     *
     * @return string[]
     */
    public function listInfo()
    {
        return $this->list(FlashMessage::INFO);
    }

    /**
     * Returns the list of all messages
     *
     * @return FlashMessage[]
     */
    public function all()
    {
        $flashMessageList = [];

        foreach ($this->session->getFlashBag()->all() as $type => $msgList) {
            $flashMessageList[$type] = [];

            foreach ($msgList as $msg) {
                $flashMessageList[$type][] = $msg;
            }
        }

        return $flashMessageList;
    }

    /**
     * Returns last message text from the list of message texts
     *
     * @param string $type
     *
     * @return FlashMessage|false
     */
    public function last(string $type)
    {
        $list = $this->list($type);

        return end($list);
    }

    /**
     * Returns first message text from the list of message texts
     *
     * @param string $type
     *
     * @return FlashMessage|false
     */
    public function first(string $type)
    {
        return current($this->list($type));
    }

}