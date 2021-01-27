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
     * @return bool
     */
    public function existsError()
    {
        return $this->session->getFlashBag()->has(FlashMessage::ERROR);
    }

    /**
     * Returns true if the message with type exists, false if not.
     *
     * @param string $type
     *
     * @return bool
     */
    public function exists(string $type)
    {
        return $this->session->getFlashBag()->has($type);
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