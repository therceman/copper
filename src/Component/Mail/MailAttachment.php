<?php


namespace Copper\Component\Mail;


/**
 * Class MailAttachment
 * @package Copper\Component\Mail
 */
class MailAttachment
{
    const ENCODING_7BIT = '7bit';
    const ENCODING_8BIT = '8bit';
    const ENCODING_BASE64 = 'base64';
    const ENCODING_BINARY = 'binary';
    const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

    protected $path;
    protected $name;
    protected $encoding;
    protected $type;
    protected $disposition;

    /**
     * MailAttachment constructor.
     * @param string $path - Path to the attachment file
     * @param string $name - Overrides the attachment name
     */
    public function __construct(string $path, $name = '')
    {
        $this->path = $path;
        $this->name = $name;

        $this->encoding = self::ENCODING_BASE64;
        $this->type = '';
        $this->disposition = 'attachment';
    }

    /**
     * @param string $path - Path to the attachment file
     * @param string $name - Overrides the attachment name
     *
     * @return MailAttachment
     */
    public static function create(string $path, $name = '')
    {
        return new self($path, $name);
    }

    /**
     * File encoding. Please use const ENCODING_*. Defaults to base64
     *
     * @param string $encoding
     *
     * @return MailAttachment
     */
    public function setEncoding(string $encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * MIME type, e.g. `image/jpeg`; determined automatically from $path if not specified
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Disposition to use. Defaults to 'attachment'
     *
     * @param string $disposition
     *
     * @return $this
     */
    public function setDisposition(string $disposition)
    {
        $this->disposition = $disposition;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getDisposition(): string
    {
        return $this->disposition;
    }

}