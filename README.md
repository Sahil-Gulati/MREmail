# MREmail
AWS SES(Simple Email Service) raw emails bulk sending. This library enables a user to gather multiple email requests and execute those requests in one go. Each email request can be altered with customized options available for generating raw email content.

## Installation
`composer require sahil-gulati/mr-email`

**OR**

```javascript
{
    "require":{
        "sahil-gulati/mr-email": "2.0.0"
    }
}
```
`composer install`

## Creating SES Email
```php
$sesEmail = new SESEmail("callback_function","AWSKEYXXXX","AWSSECRET-XXXXXX","us-east-1");
```

## Creating SES EmailRequest
```php
$sesRequestObj=new SESEmailRequest();
$sesRequestObj
    ->addReceiver($receiverEmail) 
    ->addSenderEmail($senderEmail)
    ->addSenderName($senderName)
    ->setContentType("application/json")  //Added in version 2.0.0
    ->setEmailSubject("testing email1!")
    ->setEmailBody("This is a email body")
    ->makeContent();
```

## Adding SES EmailRequest
```php
/**
 * Adding request in SESEmail
 */
$sesEmail->makeRequest($sesRequestObj);
```

## Execution
```php
/**
 * Executing gathered request
 */
$sesEmail->execute();
function callback_function($response,$requestNo,$parameters,$groupNo)
{
    print_r(func_get_args());
}
```
### Running test
```php
<?php

require_once 'vendor/autoload.php';
/**
 * Using namespace of MREmail
 */
use MREmail\SESEmail as SESEmail;
use MREmail\SESEmailRequest as SESEmailRequest;

$receiverEmail="sahil.gulati1991@outlook.com";
$senderEmail="someemail@gmail.com";
$senderName="Sahil Gulati";
/**
 * Note: While sending test email $senderEmail must be equal to $receiverEmail.
 */
$sesEmail = new SESEmail("callback_function","AWSKEYXXXX","AWSSECRET-XXXXXX","us-east-1");
$sesEmail->sendTestEmail($senderEmail);
```

## Complete example with all together
```php
<?php

require_once 'vendor/autoload.php';
/**
 * Using namespace of MREmail
 */
use MREmail\SESEmail as SESEmail;
use MREmail\SESEmailRequest as SESEmailRequest;

$receiverEmail="sahil.gulati1991@outlook.com";
$senderEmail="someemail@gmail.com";
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
            ->addSenderEmail($senderEmail)
            ->addSenderName($senderName)
            ->setContentType("application/json")
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
            ->addSenderEmail($senderEmail)
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
            ->addSenderEmail($senderEmail)
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
