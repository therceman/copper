<?php


use Copper\Component\Mail\MailConfigurator;
use Copper\Component\Mail\MailHandler;

return function (MailConfigurator $mail) {

    // --------------- How to test Gmail Mail service from localhost -------------------------
    // Open "Manage your Google Account" -> Security (Tab) -> scroll down and turn on "Less secure app access"

    /** @var string */
    $mail->host = 'smtp.gmail.com';
    /** @var int */
    $mail->port = 587;

    /** @var string */
    $mail->username;
    /** @var string */
    $mail->password;

    /** @var string */
    $mail->from;
    /** @var string */
    $mail->fromName;

    /** @var string */
    $mail->replyTo;
    /** @var string */
    $mail->replyToName;

    /** @var bool */
    $mail->isSMTPEnabled = true;
    /** @var bool */
    $mail->isSMTPAuthEnabled = true;
    /** @var bool */
    $mail->isSMTPDebugEnabled = false;
    /** @var bool */
    $mail->setSMTPDebugToClientMode = false;
    /** @var bool */
    $mail->setSMTPSecureToSTARTTLS = true;

    /** @var string */
    $mail->encoding = MailHandler::ENCODING_BASE64;
    /** @var string */
    $mail->charset = MailHandler::CHARSET_UTF8;

};