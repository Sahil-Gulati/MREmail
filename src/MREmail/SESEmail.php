<?php
namespace MREmail;
ini_set("display_errors", 1);
/**
 * This class will be used for sending Emails in Bulk for AWS.
 * @author Sahil Gulati <sahil@getamplify.com>
 * @version 1.0.0
 */
class SESEmail
{
    /**
     * @var String $hashAlgorithm Hash used in the whole
     */
    private static $hashAlgorithm = "sha256";

    /**
     * @var String $hashAlgorithm Hash used in the whole
     */
    private static $awsHashAlgorithmTag = "AWS4-HMAC-SHA256";

    /**
     * @var String $awsRequest AWS request separator
     */
    private static $awsRequestSeparator = "aws4_request";
    /**
     * @var String $accessKey SES key of AWS 
     */
    private static $accessKey="";
    /**
     * @var String SES secret of AWS 
     */
    private static $accessSecret="";
    /**
     * @var String Method used to submit on AWS 
     */
    private static $method="POST";
    /**
     * @var String URI at which request is submitted to AWS 
     */
    private static $uri="/";
    /**
     * @var String SES region of AWS 
     */
    private static $sesRegion="us-east-1";
    /**
     * @var String SES endpoint of AWS 
     */
    private static $sesEndpoint="https://email.%s.amazonaws.com";
    /**
     * @var String $awsService Name of the AWS service (i.e. email not ses)
     */
    private static $awsService="email";
    /**
     * @var FCrawling\FCrawling
     */
    protected $fcrawling=null;

    public function __construct($callback="",$accessKey="",$accessSecret="",$sesRegion="")
    {
        self::$accessKey=$accessKey;
        self::$accessSecret=$accessSecret;
        self::$sesRegion=$sesRegion;
        $this->fcrawling= new \FCrawling\FCrawling($callback);
    }
    
    /**
     * This function will add request in the an object which will execute requests at once.
     * @param \MREmail\SESEmailRequest $sesRequestObject This object will hold all the properties required to send email.
     */
    public function makeRequest(\MREmail\SESEmailRequest $sesRequestObject)
    {
        $fcrawlingRequest=new \FCrawling\FCrawlingRequest(sprintf(self::$sesEndpoint,self::$sesRegion));
        $fcrawlingRequest->setOption(array(
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $sesRequestObject->getRequestBody(),
            CURLOPT_HTTPHEADER => self::getHeaders("", $sesRequestObject->getRequestBody()),
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true
       ));
        $this->fcrawling->setRequest($fcrawlingRequest);
    }
    /**
     * This function is responsible for executing FCrawling Requests,</br>
     * And then each response is sent to callback function
     */
    public function execute()
    {
        $this->fcrawling->execute();
    }
    /**
     * This function is used to send test email. In which sender is it self the receiver.
     * @param String $senderEmail Email address of the sender.
     * @return String Response of curl request.
     */
    public function sendTestEmail($senderEmail="")
    {
        if(!empty($senderEmail))
        {
            $queryString = "";
            $payload = $this->makeTestRequest($senderEmail);
            $channel=curl_init();
            curl_setopt($channel, CURLOPT_URL, sprintf(self::$sesEndpoint, self::$sesRegion));
            curl_setopt($channel, CURLOPT_POST, true);
            curl_setopt($channel, CURLOPT_POSTFIELDS,  $payload);
            curl_setopt($channel, CURLOPT_HTTPHEADER, self::getHeaders($queryString, $payload));
            curl_setopt($channel, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($channel, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($channel, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
            $result=curl_exec($channel);
            return $result;
        }
    }
    /**
     * This function will return array of headers, which will hold required properties of authorization.
     * @return Array This function will return an array of headers.
     * @link http://docs.aws.amazon.com/amazonswf/latest/developerguide/UsingJSON-swf.html Url definiting pattern required for authorization.
     */
    private static function getHeaders($queryString, $payload)
    {
        $dateStamp = gmdate("Ymd");
        $timestamp = gmdate("Ymd\THis\Z");
        $headers[] = 'Host: ' . str_replace("https://", "", sprintf(self::$sesEndpoint, self::$sesRegion));
        $headers[] = 'X-Amz-date: ' . $timestamp;
        $signingKey = self::getSigningKey($dateStamp);
        $canonicalRequest = self::getCanonicalRequest($queryString, $headers, array("host", "x-amz-date"), $payload);
        $credentialScope = sprintf("%s/%s/%s/%s", $dateStamp, self::$sesRegion, self::$awsService, self::$awsRequestSeparator);
        $stringToSign = self::getStringToSign($timestamp, $credentialScope, $canonicalRequest);
        $signature = self::getSignature($stringToSign, $signingKey);
        array_unshift($headers, sprintf("Authorization: %s Credential=%s/%s, SignedHeaders=host;x-amz-date, Signature=%s", self::$awsHashAlgorithmTag, self::$accessKey, $credentialScope, $signature));
        array_unshift($headers, "Accept: application/json");
        return $headers;
    }
    private static function getSignature($stringToSign, $signingKey) {
        $signature = hash_hmac("sha256", $stringToSign, $signingKey);
        return $signature;
    }
    private static function getSigningKey($dateStamp) {
        return  hash_hmac(
            self::$hashAlgorithm,
            self::$awsRequestSeparator,
            hash_hmac(
                self::$hashAlgorithm,
                self::$awsService,
                hash_hmac(
                    self::$hashAlgorithm,
                    self::$sesRegion,
                    hash_hmac(
                        self::$hashAlgorithm, 
                        $dateStamp,
                        "AWS4".self::$accessSecret,
                        true), 
                    true), 
                true), 
            true);
    }
    private static function getCanonicalRequest($queryString, $headers, $signedHeaders, $payload="") {
        $headers = array_map(function($header) {
            $splits = explode(": ", $header);
            return sprintf("%s:%s",strtolower($splits[0]), $splits[1]);
        }, $headers);
        return hash(
            self::$hashAlgorithm,
            join(
                array(
                    self::$method,
                    self::$uri,
                    $queryString,
                    join($headers, "\n") . "\n",
                    join($signedHeaders, ";"),
                    hash(self::$hashAlgorithm, $payload),
                ),
                "\n"
            )
        );
    }
    private static function getStringToSign($timestamp, $credentialScope, $canonicalRequest) {
        return join(
            array(
                self::$awsHashAlgorithmTag, 
                $timestamp,
                $credentialScope,
                $canonicalRequest
            ),
            "\n"
        );
    }
    /**
     * This function will generate a test request for sending email.
     * @param String $senderEmail Email address of the sender.
     * @return \MREmail\SESEmailRequest This object will coupled properites of the email.
     */
    private function makeTestRequest($senderEmail="")
    {
        return (new SESEmailRequest())
                ->addSenderEmail($senderEmail)
                ->addReceiver($senderEmail)
                ->addSenderName("Sahil Gulati")
                ->setEmailSubject("Testing email!")
                ->setEmailBody("Hello World")
                ->addCustomHeader("X-Developer-email", "sahil.gulati1991@outlook.com")
                ->addCustomHeader("X-Developer-Id", "github/sahil-gulati")
                ->makeContent(true);
    }
    
}
/**
 * This class is responsible for creating request in some standard format.
 */
class SESEmailRequest
{
    /**
     * @var String It will contain encoded email content.
     */
    protected $content="";
    /**
     * @var String It will contain email body.
     */
    protected $emailBody="";
    /**
     * @var String It will contain sender name.
     */
    protected $senderName="";
    /**
     * @var String It will contain sender email address.
     */
    protected $senderEmail="";
    /**
     * @var String It will contain email subject.
     */
    protected $emailSubject="";
    /**
     * @var String It will contain receiver email address.
     */
    protected $receiverEmail="";
    
    /**
     * @var Boolean Determines whether email body is in HTML format or not. 
     */
    protected $isHtml=false;
    /**
     * @var Array This will contain custom headers. 
     */
    protected $customHeader=array();
    /**
     * This function will set receiver email.
     * @param String $receiverEmail Email address of the receiver.
     * @return \MREmail\SESEmailRequest
     */
    public function addReceiver($receiverEmail)
    {
        if(!empty($receiverEmail))
        {
            $this->receiverEmail=$receiverEmail;
        }
        return $this;
    }
    /**
     * This function will set sender email.
     * @param String $senderEmail Email address of the sender.
     * @return \MREmail\SESEmailRequest
     */
    public function addSenderEmail($senderEmail)
    {
        if(!empty($senderEmail))
        {
            $this->senderEmail=$senderEmail;
        }
        return $this;
    }
    /**
     * This function will set sender name with the email.
     * @param String $senderName Name of the sender
     * @return \MREmail\SESEmailRequest
     */
    public function addSenderName($senderName)
    {
        if(!empty($senderName))
        {
            $this->senderName=$senderName;
        }
        return $this;
    }
    /**
     * This function will set custom headers with the body.
     * @param String $headerName Name of the header.
     * @param String $headerValue Value of the header.
     * @return \MREmail\SESEmailRequest
     */
    public function addCustomHeader($headerName,$headerValue)
    {
        if(!empty($headerName) && !empty($headerValue))
        {
            if(is_string($headerName) && is_string($headerValue))
            {
                $this->customHeader[$headerName]=$headerValue;
            }
        }
        return $this;
    }
    /**
     * This function will set email body.
     * @param String $body Body of the email.
     * @param Boolean $isHtml For setting whether email body is in HTML format or not.
     * @return \MREmail\SESEmailRequest
     */
    public function setEmailBody($body,$isHtml=false)
    {
        if(!empty($body))
        {
            $this->emailBody=$body;
        }
        $this->isHtml=$isHtml;
        return $this;
    }
    /**
     * This function will set email subject.
     * @param String $subject Subject of the email.
     * @return \MREmail\SESEmailRequest
     */
    public function setEmailSubject($subject)
    {
        if(!empty($subject))
        {
            $this->emailSubject=$subject;
        }
        return $this;
    }
    /**
     * This will return email encoded content.
     * @return String Email encoded content.
     */
    public function getRequestBody()
    {
        return $this->content;
    }
    /**
     * This function is responsible for generation encoded string which will be sent to AWS for sending email.
     * @param Boolean $return If it is set to true and encoded content will be returned.
     * @return \MREmail\SESEmailRequest
     */
    public function makeContent($return=false)
    {
        $senderName=!empty($this->senderName) ? $this->senderName : explode("@", $this->senderEmail)[0];
        $mail= new \PHPMailer();
        $mail->CharSet = "UTF-8";
        $mail->AddAddress($this->receiverEmail);
        $mail->setFrom($this->senderEmail, $senderName);
        foreach($this->customHeader as $headerKey => $headerValue)
        {
            $mail->addCustomHeader($headerKey, $headerValue);
        }
        $mail->addCustomHeader("X-Developer-email", "sahil.gulati1991@outlook.com");
        $mail->addCustomHeader("X-Developer-Id", "github/sahil-gulati");
        $mail->Subject = $this->emailSubject;
        $mail->Body = $this->emailBody;
        if($this->isHtml)
        {
            $mail->isHTML(true);
        }
        $mail->preSend();
        $mime=$mail->getSentMIMEMessage();
        $rawData=base64_encode($mime);
        $params[]="Action=".str_replace('%7E', '~', rawurlencode("SendRawEmail"));;
        $params[]="RawMessage.Data=".str_replace('%7E', '~', rawurlencode($rawData));
        $this->content=implode('&', $params);
        if($return===true)
        {
            return implode('&', $params);
        }
        return $this;
    }
}
