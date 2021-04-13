<?php

/**
 * This file is just for test and example purpose
 * @author Sahil Gulati <sahil.gulati1991@outlook.com>
 */
require_once 'vendor/autoload.php';

use MREmail\SESEmail as SESEmail;
use MREmail\SESEmailRequest as SESEmailRequest;

$receiverEmail="sahil@getamplify.com";
$senderName="Sahil Gulati";
$sesEmail = new SESEmail('test_function',"AWSKEYXXXX","AWSSECRETXXXX","us-east-1");
$sesEmail->makeRequest(
            (new SESEmailRequest())
            ->addReceiver($receiverEmail)
            ->addSenderEmail($receiverEmail)
            ->addSenderName($senderName)
            ->setEmailSubject("testing email1!")
            ->setEmailBody("This is a email body")
            ->makeContent()
        );
$sesEmail->makeRequest(
            (new SESEmailRequest())
            ->addReceiver($receiverEmail)
            ->addSenderEmail($receiverEmail)
            ->addSenderName($senderName)
            ->setEmailSubject("testing email2!")
            ->setEmailBody("This is a email body")
            ->makeContent()
        );
$sesEmail->makeRequest(
            (new SESEmailRequest())
            ->addReceiver($receiverEmail)
            ->addSenderEmail($receiverEmail)
            ->addSenderName($senderName)
            ->setEmailSubject("testing email3!")
            ->setEmailBody("This is a email body")
            ->makeContent()
        );
print_r($sesEmail->execute());
function test_function($response,$requestNo,$parameters,$groupNo)
{
    print_r(func_get_args());
}
