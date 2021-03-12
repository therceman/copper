<?php

namespace Copper\Component\Mail;

use Copper\FunctionResponse;
use Copper\Handler\StringHandler;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailHandler
{
    /** @var MailConfigurator */
    public $config;

    /**
     * CPHandler constructor.
     *
     * @param MailConfigurator $projectConfig
     * @param MailConfigurator $packageConfig
     */
    public function __construct(MailConfigurator $packageConfig, MailConfigurator $projectConfig = null)
    {
        $this->config = $this->mergeConfig($packageConfig, $projectConfig);
    }

    private function mergeConfig(MailConfigurator $packageConfig, MailConfigurator $projectConfig = null)
    {
        if ($projectConfig === null)
            return $packageConfig;

        $vars = get_object_vars($projectConfig);

        foreach ($vars as $key => $value) {
            if ($value !== null || trim($value) !== "")
                $packageConfig->$key = $value;
        }

        return $packageConfig;
    }

    public function send(Mail $mail)
    {
        $response = new FunctionResponse();

        $mailService = new PHPMailer();

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
        if (StringHandler::isEmpty($this->config->host))
            return $response->error('Host is not defined');
        else
            $mailService->Host = $this->config->host;

        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        if (StringHandler::isEmpty($this->config->port))
            return $response->error('Port is not defined');
        else
            $mailService->Port = $this->config->port;

        //Set the encryption mechanism to use - STARTTLS or SMTPS
        if ($this->config->setSMTPSecureToSTARTTLS)
            $mailService->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        else
            $mailService->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

        //Whether to use SMTP authentication
        if ($this->config->isSMTPAuthEnabled)
            $mailService->SMTPAuth = true;

        //Username to use for SMTP authentication - use full email address for gmail
        if (StringHandler::isEmpty($this->config->username))
            return $response->error('Username is not defined');
        else
            $mailService->Username = $this->config->username;

        //Password to use for SMTP authentication
        if (StringHandler::isEmpty($this->config->password))
            return $response->error('Password is not defined');
        else
            $mailService->Password = $this->config->password;

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