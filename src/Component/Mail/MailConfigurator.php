<?php


namespace Copper\Component\Mail;

class MailConfigurator
{
    /** @var string */
    public $host;
    /** @var int */
    public $port;
    /** @var string */
    public $username;
    /** @var string */
    public $password;

    /** @var string */
    public $from;
    /** @var string */
    public $fromName;

    /** @var string */
    public $replyTo;
    /** @var string */
    public $replyToName;

    /** @var bool */
    public $isSMTPEnabled;
    /** @var bool */
    public $isSMTPAuthEnabled;
    /** @var bool */
    public $isSMTPDebugEnabled;
    /** @var bool */
    public $setSMTPDebugToClientMode;
    /** @var bool */
    public $setSMTPSecureToSTARTTLS;

}