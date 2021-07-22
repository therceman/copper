<?php

namespace Copper\Component\Mail;

use Copper\FunctionResponse;
use Copper\Handler\StringHandler;
use Copper\Traits\ComponentHandlerTrait;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Class MailHandler
 * @package Copper\Component\Mail
 */
class MailHandler
{
    use ComponentHandlerTrait;

    /** @var MailConfigurator */
    public $config;

    const ENCODING_7BIT = '7bit';
    const ENCODING_8BIT = '8bit';
    const ENCODING_BASE64 = 'base64';
    const ENCODING_BINARY = 'binary';
    const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

    const CHARSET_ASCII = 'us-ascii';
    const CHARSET_ISO88591 = 'iso-8859-1';
    const CHARSET_UTF8 = 'utf-8';

    /**
     * MailHandler constructor.
     *
     * @param string $configFilename
     * @param MailConfigurator|null $config
     */
    public function __construct(string $configFilename, MailConfigurator $config = null)
    {
        $this->config = $config ?? $this->configure(MailConfigurator::class, $configFilename);
    }

    /**
     * @param Mail $mail
     *
     * @return FunctionResponse
     */
    public function send(Mail $mail)
    {
        $response = new FunctionResponse();

        $mailService = new PHPMailer();

        // Set charset
        $charset = $this->config->charset;
        if (StringHandler::isNotEmpty($charset))
            $mailService->CharSet = $charset;

        // Set encoding
        $encoding = $this->config->encoding;
        if (StringHandler::isNotEmpty($encoding))
            $mailService->Encoding = $encoding;

        //Tell PHPMailer to use SMTP
        if ($this->config->isSMTPEnabled)
            $mailService->isSMTP();

        //Enable SMTP debugging
        //SMTP::DEBUG_OFF = off (for production use)
        //SMTP::DEBUG_CLIENT = client messages
        //SMTP::DEBUG_SERVER = client and server messages
        if ($this->config->isSMTPDebugEnabled)
            $mailService->SMTPDebug = ($this->config->setSMTPDebugToClientMode) ? SMTP::DEBUG_CLIENT : SMTP::DEBUG_SERVER;
        else
            $mailService->SMTPDebug = SMTP::DEBUG_OFF;

        //Set the hostname of the mail server
        //Use `$mailService->Host = gethostbyname('smtp.gmail.com');`
        //if your network does not support SMTP over IPv6,
        //though this may cause issues with TLS
        $host = $this->config->host;
        if (StringHandler::isEmpty($host))
            return $response->error('Host is not defined');
        else
            $mailService->Host = $host;

        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $port = $this->config->port;
        if (StringHandler::isEmpty($port))
            return $response->error('Port is not defined');
        else
            $mailService->Port = $port;

        //Set the encryption mechanism to use - STARTTLS or SMTPS
        if ($this->config->setSMTPSecureToSTARTTLS)
            $mailService->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        else
            $mailService->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

        //Whether to use SMTP authentication
        if ($this->config->isSMTPAuthEnabled)
            $mailService->SMTPAuth = true;

        //Username to use for SMTP authentication - use full email address for gmail
        $username = $this->config->username;
        if (StringHandler::isEmpty($username))
            return $response->error('Username is not defined');
        else
            $mailService->Username = $username;

        //Password to use for SMTP authentication
        $password = $this->config->password;
        if (StringHandler::isEmpty($password))
            return $response->error('Password is not defined');
        else
            $mailService->Password = $password;

        //Set who the message is to be sent from
        try {
            $cfgFrom = $this->config->from;
            $cfgFromName = $this->config->fromName;

            $from = (StringHandler::isEmpty($cfgFrom)) ? false : $cfgFrom;
            $fromName = (StringHandler::isEmpty($cfgFromName)) ? '' : $cfgFromName;

            if ($from !== false)
                $mailService->setFrom($from, $fromName);
            elseif ($fromName !== '')
                $mailService->FromName = $fromName;
        } catch (Exception $e) {
            return $response->error('setFrom: ' . $e->errorMessage());
        }

        //Set an alternative reply-to address
        try {
            $cfgReplyTo = $this->config->replyTo;
            $cfgReplyToName = $this->config->replyToName;

            $replyTo = (StringHandler::isEmpty($cfgReplyTo)) ? false : $cfgReplyTo;
            $replyToName = (StringHandler::isEmpty($cfgReplyToName)) ? '' : $cfgReplyToName;

            if ($replyTo !== false)
                $mailService->addReplyTo($replyTo, $replyToName);

        } catch (Exception $e) {
            return $response->error('addReplyTo: ' . $e->errorMessage());
        }

        //Set who the message is to be sent to
        try {
            $mailService->addAddress($mail->getAddress(), $mail->getAddressName());
        } catch (Exception $e) {
            return $response->error('addAddress: ' . $e->errorMessage());
        }

        //Set the subject line
        $subject = $mail->getSubject();
        if (StringHandler::isNotEmpty($subject))
            $mailService->Subject = $subject;

        //Set the body/htmlBody line
        $body = $mail->getBody();
        $htmlBody = $mail->getHtmlBody();
        if (StringHandler::isNotEmpty($htmlBody)) {
            try {
                $mailService->msgHTML($htmlBody, $mail->getHtmlBodyBaseDir());
            } catch (Exception $e) {
                return $response->error('msgHTML: ' . $e->errorMessage());
            }
        } elseif (StringHandler::isNotEmpty($body)) {
            $mailService->Body = $body;
        }

        //Replace the plain text body with one created manually
        $altBody = $mail->getAltBody();
        if (StringHandler::isNotEmpty($altBody))
            $mailService->AltBody = $altBody;

        //Attach a file
        foreach ($mail->getAttachmentList() as $attachment) {
            $path = $attachment->getPath();

            try {

                $result = $mailService->addAttachment(
                    $path,
                    $attachment->getName(),
                    $attachment->getEncoding(),
                    $attachment->getType(),
                    $attachment->getDisposition()
                );

                if ($result === false)
                    return $response->error('addAttachment: (' . $path . ') failed.');
            } catch (Exception $e) {
                return $response->error('addAttachment: (' . $path . ') ' . $e->errorMessage());
            }
        }

        try {
            $sendStatus = $mailService->send();

            if ($sendStatus === false)
                return $response->error('send: failed - ' . $mailService->ErrorInfo);
        } catch (Exception $e) {
            $response->error('send: ' . $e->errorMessage());
        }

        return $response->ok();
    }

}