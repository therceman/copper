<?php


namespace Copper\Component\Mail;

/**
 * Class MailConfigurator
 * @package Copper\Component\Mail
 */
class MailConfigurator
{
    /** @var string Host (SMTP). Example: smtp.gmail.com` */
    public $host;
    /** @var int Port. Example: 587 */
    public $port;
    /** @var string */
    public $username;
    /** @var string */
    public $password;

    /** @var string [optional] */
    public $from;
    /** @var string [optional] */
    public $fromName;

    /** @var string [optional] */
    public $replyTo;
    /** @var string [optional] */
    public $replyToName;

    /** @var bool [optional] = true */
    public $isSMTPEnabled;
    /** @var bool [optional] = true */
    public $isSMTPAuthEnabled;
    /** @var bool [optional] = false */
    public $isSMTPDebugEnabled;
    /** @var bool [optional] = false */
    public $setSMTPDebugToClientMode;
    /** @var bool [optional] = true */
    public $setSMTPSecureToSTARTTLS;

    /** @var string [optional] = base64 <p>Mail Encoding</p> */
    public $encoding;
    /** @var string [optional] = utf-8 <p>Mail Charset</p>*/
    public $charset;

}