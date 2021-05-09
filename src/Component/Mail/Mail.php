<?php


namespace Copper\Component\Mail;


use Copper\FunctionResponse;
use Copper\Kernel;

/**
 * Class Mail
 * @package Copper\Component\Mail
 */
class Mail
{
    /** @var string */
    protected $address;
    /** @var string */
    protected $addressName;
    /** @var string */
    protected $subject;
    /** @var string */
    protected $body;
    /** @var string */
    protected $altBody;
    /** @var string */
    protected $htmlBody;
    /** @var string */
    protected $htmlBody_baseDir;
    /** @var MailAttachment[] */
    protected $attachmentList;

    /**
     * Mail constructor.
     * @param string $address
     * @param string $name
     */
    public function __construct(string $address, string $name = '')
    {
        $this->address = $address;
        $this->addressName = $name;

        $this->htmlBody_baseDir = '';
        $this->attachmentList = [];
    }

    /**
     * @param string $address
     * @param string $name
     * @return Mail
     */
    public static function create(string $address, $name = '')
    {
        return new self($address, $name);
    }

    /**
     * Set the subject line
     *
     * @param $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Set plain text body
     *
     * @param string $body
     * @return $this
     */
    public function setBody(string $body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Set plain text alternative body. Only used with plain text body (not HTML body)
     *
     * @param string $altBody
     * @return $this
     */
    public function setAltBody(string $altBody)
    {
        $this->altBody = $altBody;

        return $this;
    }

    /**
     * Create a message body and alternative body from HTML string.
     *
     * @param $htmlBody
     * @param $baseDir
     * @return $this
     */
    public function setHtmlBody($htmlBody, $baseDir = __DIR__)
    {
        $this->htmlBody = $htmlBody;
        $this->htmlBody_baseDir = $baseDir;

        return $this;
    }

    /**
     * @param MailAttachment $attachment
     *
     * @return $this
     */
    public function addAttachment(MailAttachment $attachment)
    {
        $this->attachmentList[] = $attachment;

        return $this;
    }

    /**
     * @return FunctionResponse
     */
    public function send()
    {
        return Kernel::getMail()->send($this);
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getAddressName()
    {
        return $this->addressName;
    }

    /**
     * @return string|null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @return string|null
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string|null
     */
    public function getAltBody()
    {
        return $this->altBody;
    }

    /**
     * @return string|null
     */
    public function getHtmlBody()
    {
        return $this->htmlBody;
    }

    /**
     * @return string
     */
    public function getHtmlBodyBaseDir(): string
    {
        return $this->htmlBody_baseDir;
    }

    /**
     * @return MailAttachment[]
     */
    public function getAttachmentList(): array
    {
        return $this->attachmentList;
    }

}