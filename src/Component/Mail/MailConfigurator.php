<?php


namespace Copper\Component\Mail;

/**
 * Class MailConfigurator
 * <hr>
 * How to test Gmail Mail service from localhost:
 * <p>Open "Manage your Google Account" -> Security (Tab) -> scroll down and turn on "Less secure app access"</p>
 * <p>Make sure google account doesn't have 2FA enabled</p>
 *
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

    /** @var string [optional] <p>Reply to address</p>*/
    public $replyTo;
    /** @var string [optional] <p>Reply to name</p>*/
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