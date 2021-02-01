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
        $this->session->getFlashBag()->add($type, $text);
    }

    /**
     * @param string $text
     */
    public function setError(string $text)
    {
        $this->session->getFlashBag()->add(FlashMessage::ERROR, $text);
    }

    /**
     * @param string $text
     */
    public function setSuccess(string $text)
    {
        $this->session->getFlashBag()->add(FlashMessage::SUCCESS, $text);
    }

    /**
     * @param string $text
     */
    public function setInfo(string $text)
    {
        $this->session->getFlashBag()->add(FlashMessage::INFO, $text);
    }

    /**
     * @param string $text
     */
    public function setWarning(string $text)
    {
        $this->session->getFlashBag()->add(FlashMessage::WARNING, $text);
    }

    /**
     * Get first message text by type
     *
     * @param string $type
     *
     * @return string|false
     */
    public function get(string $type)
    {
        return $this->first($type);
    }

    /**
     * @return FlashMessage|false
     */
    public function getError()
    {
        return $this->first(FlashMessage::ERROR);
    }

    /**
     * @return FlashMessage|false
     */
    public function getSuccess()
    {
        return $this->first(FlashMessage::SUCCESS);
    }

    /**
     * @return FlashMessage|false
     */
    public function getInfo()
    {
        return $this->first(FlashMessage::INFO);
    }

    /**
     * @return FlashMessage|false
     */
    public function getWarning()
    {
        return $this->first(FlashMessage::WARNING);
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
     * Returns the list of all messages
     *
     * @return FlashMessage[]
     */
    public function all()
    {
        $flashMessageList = [];

        foreach ($this->session->getFlashBag()->all() as $type => $msg) {
            $flashMessageList[] = new FlashMessage($type, $msg);
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