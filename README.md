# MREmail
AWS SES raw emails bulk sending. This library enables a user to gather multiple email requests and execute those requests in one go. Each email request can be altered with customized options available for generating raw email content.

##Installation
`composer require sahil-gulati/mr-email`

**OR**

```javascript
{
    "require":{
        "sahil-gulati/mr-email": "1.0.0"
    }
}
```
`composer install`

##Usage
```php
<?php

require_once 'vendor/autoload.php';
/**
 * Using namespace of MREmail
 */
use MREmail\SESEmail as SESEmail;
use MREmail\SESEmailRequest as SESEmailRequest;

$receiverEmail="sahil@getamplify.com";
$senderName="Sahil Gulati";
/**
 * Initiating object of SESEmail
 * Callback function type
 * (String) `callback_function` global function
 * (Array) array => 0 (Object) $classObject array => 1 (String) function_name(public) 
 * (Array) array => 0 (String) class_name array => 1 (String) function_name(public static) 
 */
$sesEmail = new SESEmail("callback_function","AWSKEYXXXX","AWSSECRET-XXXXXX","email.us-east-1.amazonaws.com");
/**
 * Initiating object of SESEmail request 1
 */
$sesRequestObj=new SESEmailRequest();
/**
 * Adding request in SESEmail
 */
$sesEmail->makeRequest(
            $sesRequestObj
            ->addReceiver($receiverEmail)
            ->addSenderEmail($receiverEmail)
            ->addSenderName($senderName)
            ->setEmailSubject("testing email1!")
            ->setEmailBody("This is a email body")
            ->makeContent()
        );
/**
 * Initiating object of SESEmail request 2
 */
$sesRequestObj=new SESEmailRequest();
/**
 * Adding request in SESEmail
 */
$sesEmail->makeRequest(
            $sesRequestObj
            ->addReceiver($receiverEmail)
            ->addSenderEmail($receiverEmail)
            ->addSenderName($senderName)
            ->setEmailSubject("testing email2!")
            ->setEmailBody("This is a email body")
            ->makeContent()
        );
/**
 * Initiating object of SESEmail request 3
 */
$sesRequestObj=new SESEmailRequest();
/**
 * Adding request in SESEmail
 */
$sesEmail->makeRequest(
            $sesRequestObj
            ->addReceiver($receiverEmail)
            ->addSenderEmail($receiverEmail)
            ->addSenderName($senderName)
            ->setEmailSubject("testing email3!")
            ->setEmailBody("This is a email body")
            ->makeContent()
        );
/**
 * Executing gathered request
 */
$sesEmail->execute();
function callback_function($response,$requestNo,$parameters,$groupNo)
{
    print_r(func_get_args());
}

?>
```
