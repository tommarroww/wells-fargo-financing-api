<?php
/************************************************************************************************
* Copyright (C)2011 - 2018 littletzar - All Rights Reserved                                     *
* Unauthorized reproduction of this File in whole or part via any medium is strictly prohibited *
* Proprietary and confidential                                                                  *
* Written by Joshua Dale <littletzar@littletzar.com> - littletzar.com                           *
* For use with the Wells Fargo Financing API only                                               *
************************************************************************************************/

namespace WellsFargo;

require_once dirname(__FILE__).'/file.class.php';
require_once dirname(__FILE__).'/format.class.php';

/**
* This class manages transactions with Wells Fargo Retail Services.
*
* @author Joshua Dale
* @created 20180810
* @modified 20180815
*/

class WellsFargoC
{
  //intialize boundary and default properties
  //protected $accountNumberStringLength = 16;
  protected $accountNumberMax                = 9999999999999999;
  protected $accountNumberMin                = 1000000000000000;
  protected $amountMax                       = 99999999.99;
  protected $amountMin                       = 0.00;
  protected $authorizationNumberMax          = 999999;
  protected $authorizationNumberMin          = 0;
  protected $authorizationNumberStringLength = 6;
  protected $merchantNumberMax               = 999999999999999;
  protected $merchantNumberMin               = 100000000000000;
  //protected $merchantNumberStringLength = 15;
  protected $localeDefault = 'en_US';
  protected $localeOptions =
  [
    'en_CA',
    'en_US',
    'fr_CA'
  ];
  protected $planNumberMax = 9999;
  protected $planNumberMin = 1000;
  protected $requestDirectoryDefault = 'requests/'; //directory inside wells-fargo directory
  protected $responseDirectoryDefault = 'responses/'; //directory inside wells-fargo directory
  protected $settableProperties =
  [
    'accountNumber',
    'dealerId',
    'locale',
    'merchantNumber',
    'password',
    'recordRequest',
    'recordRequestDirectory',
    'recordResponse',
    'recordResponseDirectory',
    'username',
    'wsdlUrlToUse'
  ];
  protected $ticketNumberStringLengthMax = 12;
  protected $wsdlUrlTest = 'https://retailservices-uat.wellsfargo.com/services/SubmitTransactionService';
  protected $wsdlUrlProduction = 'https://retailservices.wellsfargo.com/services/SubmitTransactionService';
  protected $wsdlUrl = null;

  //initialize settable properties
  protected $accountNumber = null;
  protected $dealerId = null; //not currently used
  protected $locale = null;
  protected $merchantNumber = null;
  protected $password = null;
  protected $recordRequest = 'directory';
  protected $recordResponse = 'directory';
  protected $requestDirectory = null;
  protected $responseDirectory = null;
  protected $username = null;
  protected $wsdlUrlToUse = null;

  //initialize non-settable properties
  protected $errors = []; //list dictionary of errors
  protected $requests = [];  //list dictionary of requests
  protected $responses = [];  //list dictionary of response
  protected $requrestI = -1; //index of requests
  protected $unique = null; //unique value created for every request

  /*****************
  * Public Methods *
  *****************/

  public function __construct($properties = null)
  {
    //set defined properties
    if(defined('WELLS_FARGO_PASSWORD'))
      $this->password = WELLS_FARGO_PASSWORD;

    if(defined('WELLS_FARGO_RECORD_REQUEST'))
      $this->recordRequest = WELLS_FARGO_RECORD_REQUEST;

    if(defined('WELLS_FARGO_RECORD_RESPONSE'))
      $this->recordResponse = WELLS_FARGO_RECORD_RESPONSE;

    if(defined('WELLS_FARGO_REQUEST_DIRECTORY'))
      $this->requestDirectory = WELLS_FARGO_REQUEST_DIRECTORY;

    if(defined('WELLS_FARGO_RESPONSE_DIRECTORY'))
      $this->responseDirectory = WELLS_FARGO_RESPONSE_DIRECTORY;

    if(defined('WELLS_FARGO_USERNAME'))
      $this->username = WELLS_FARGO_USERNAME;

    if(defined('WELLS_FARGO_WSDL_URL_TO_USE'))
      $this->wsdlUrlToUse = WELLS_FARGO_WSDL_URL_TO_USE;

    $this->setter($properties);
  }

  /**
  * This method charges an account.
  *
  * @param float amount - The amount between 0.00 and 99,999,999.99 to charge
  * @param int planNumber - The plan number greater than 1000 to add to the account
  * @param [string] ticketNumber - A ticket number to associate with the transaction
  * @return array - The data returned form Wells Fargo
  */

  public function charge($amount, $planNumber, $ticketNumber = null)
  {
    return $this->sendRequest(1, $amount, $planNumber, $ticketNumber);
  }

  /**
  * This methods gets the errors for the requested or last request.
  */

  public function getErrors($i = null)
  {
    $i = FormatC::number($i, false, false, $this->requestI);

    if(array_key_exists($i, $this->errors))
      return $this->errors[$i];

    return 'Error set for request '.$i.' not found.';
  }

  /**
  * This method refunds an account.
  *
  * @param float amount - The amount between 0.00 and 99,999,999.99 to refund
  * @param int planNumber - The plan number greater than 1000 to refund from
  * @param [string] ticketNumber - A ticket number to associate with the transaction
  */

  public function refund($amount, $planNumber, $ticketNumber = null)
  {
    return $this->sendRequest(4, $amount, $planNumber, $ticketNumber);
  }

  /**
  * This methods gets the request for the requested or last request.
  */

  public function getRequest($i = null)
  {
    $i = FormatC::number($i, false, false, $this->requestI);

    if(array_key_exists($i, $this->requests))
      return $this->requests[$i];

    return 'Request for request '.$i.' not found.';
  }

  /**
  * This methods gets the response for the requested or last request.
  */

  public function getResponse($i = null)
  {
    $i = FormatC::number($i, false, false, $this->requestI);

    if(array_key_exists($i, $this->responses))
      return $this->responses[$i];

    return 'Response for request '.$i.' not found.';
  }

  /**
  * This method sets the supplied properties for this object.
  *
  * @param array properties - A list of properties to set
  */

  public function setter($properties)
  {
    if(is_array($properties)) //set listed of properties
      foreach($properties as $property => $value)
        if(in_array($property, $this->settableProperties) && property_exists($this, $property))
          if($property == 'requestDirectory' || $property == 'responseDirectory')
            $this->{$property} = rtrim($value, '/').'/';
          else
            $this->{$property} = $value;
  }

  /*********************
  * End Public Methods *
  *********************/

  /********************
  * Protected Methods *
  ********************/

  /**
  * This method gets the request directory.
  *
  * @return string
  */

  protected function getRequestDirectory()
  {
    if($this->requestDirectory)
      return $this->requestDirectory;

    return dirname(__FILE__).'/'.$this->requestDirectoryDefault;
  }

  /**
  * This method gets the response directory.
  *
  * @return string
  */

  protected function getResponseDirectory()
  {
    if($this->responseDirectory)
      return $this->responseDirectory;

    return dirname(__FILE__).'/'.$this->responseDirectoryDefault;
  }

  /**
  * This method verifies data is valid and prepares it to be sent in a request.
  *
  * @param float amount - The amount between 0.00 and 99,999,999.99 to refund
  * @param int planNumber - The plan number greater than 1000 to refund from
  * @param string ticketNumber - A ticket number to associate with the transaction
  * @param int authorizationNumber - The authorization number needed to approve the transaction
  * @return bool|array - If errors: false Else: Dictionary of data
  */

  protected function prepareRequestData($amount, $planNumber, $ticketNumber, $authorizationNumber)
  {
    //amount
    if($amount)
    {
      $amount = FormatC::currency($amount);

      if($amount === null)
        $this->errors[$this->requestI]['amount'] = 'Invalid amount value.';
      else if($amount < $this->amountMin)
        $this->errors[$this->requestI]['amount'] = 'Amount must be greater than '.$this->amountMin.'.';
      else if($amount > $this->amountMax)
        $this->errors[$this->requestI]['amount'] = 'Amount must be less than '.$this->amountMax.'.';
    }
    else
      $this->errors[$this->requestI]['amount'] = 'An amount must be supplied.';

    //authorization number
    if($authorizationNumber)
    {
      $authorizationNumber = FormatC::number($authorizationNumber);

      if($authorizationNumber === null)
        $this->errors[$this->requestI]['authorizationNumber'] = 'Invalid authorization number.';
      else if($authorizationNumber < $this->authorizationNumberMin)
        $this->errors[$this->requestI]['authorizationNumber'] = 'Authorization number must be greater than '.$this->authorizationNumberMin.'.';
      else if($authorizationNumber > $this->authorizationNumberMax)
        $this->errors[$this->requestI]['authorizationNumber'] = 'Authorization number must be less than '.$this->authorizationNumberMax.'.';
    }
    else
      $this->errors[$this->requestI]['authorizationNumber'] = 'An authorization number must be supplied.';

    //plan number
    if($planNumber)
    {
      $planNumber = FormatC::number($planNumber);

      if($planNumber === null)
        $this->errors[$this->requestI]['planNumber'] = 'Invalid plan number.';
      else if($planNumber < $this->planNumberMin)
        $this->errors[$this->requestI]['planNumber'] = 'Plan number '.$planNumber.' must be greater than '.$this->planNumberMin.'.';
      else if($planNumber > $this->planNumberMax)
        $this->errors[$this->requestI]['planNumber'] = 'Plan number '.$planNumber.' must be less than '.$this->planNumberMax.'.';
    }
    else
      $this->errors[$this->requestI]['planNumber'] = 'A plan number must be supplied.';

    //ticket number
    if(strlen($ticketNumber) > $this->ticketNumberStringLengthMax)
      $this->errors[$this->requestI]['ticketNumber'] = 'Ticket number '.$ticketNumber.' must be less than '.$this->ticketNumberStringLengthMax.' characters.';

    if($this->errors[$this->requestI])
      return false;

    return
    [
      'amount' => $amount,
      'authorizationNumber' => str_pad($authorizationNumber, $this->authorizationNumberStringLength, '0', STR_PAD_LEFT),
      'planNumber' => $planNumber,
      'transactionCode' => $transactionCode
    ];
  }

  /**
  * This method records the requests that are sent.
  */

  protected function recordRequest($request, $headers)
  {
    $this->requests[$this->requestI] =
    [
      'request' => $request,
      'headers' => $headers
    ];

    if($this->recordRequest == 'directory')
      FileC::putContents(implode(PHP_EOL, $headers).PHP_EOL.PHP_EOL.str_replace('><', '>'.PHP_EOL.'<', $request), $this->unique.'-request.php', $this->getRequestDirectory());
  }

  /**
  * This method records the responses that come back.
  */

  protected function recordResponse($response, $headers)
  {
    $this->responses[$this->requestI] =
    [
      'response' => $response,
      'headers' => $headers
    ];

    if($this->recordResponse == 'directory')
      FileC::putContents(var_export($headers, true).PHP_EOL.PHP_EOL.str_replace('><', '>'.PHP_EOL.'<', $response), $this->unique.'-response.php', $this->getResponseDirectory());
  }

  /**
  * This method verifies required data is set, valid, and prepares it to be sent in a request.
  *
  * @return bool - If errors: false Else: true
  */

  protected function requiredDataIsSet()
  {
    //account number
    if($this->accountNumber)
    {
      $this->accountNumber = FormatC::number($this->accountNumber);

      if($this->accountNumber === null)
        $this->errors[$this->requestI]['accountNumber'] = 'Invalid account number.';
      else if($this->accountNumber < $this->accountNumberMin)
        $this->errors[$this->requestI]['accountNumber'] = 'Account number '.$this->accountNumber.' must be greater than '.$this->accountNumberMin.'.';
      else if($this->accountNumber > $this->accountNumberMax)
        $this->errors[$this->requestI]['accountNumber'] = 'Account number '.$this->accountNumber.' must be less than '.$this->accountNumberMax.'.';

      //$this->accountNumber = str_pad($this->accountNumber, $this->accountNumberStringLength, '0', STR_PAD_LEFT);
    }
    else
      $this->errors[$this->requestI]['accountNumber'] = 'An account number must be supplied.';

    //locale
    if(!strlen($this->locale))
      $this->locale = $this->localeDefault;
    else if(!in_array($this->locale, $this->localeOptions))
      $this->errors[$this->requestI]['locale'] = 'Invalid locale.  Possible locale\'s are '.implode(', ', $this->localeOptions).'.';

    //merchant number
    if($this->merchantNumber || (defined('WELLS_FARGO_MERCHANT_NUMBER') && ($this->merchantNumber = WELLS_FARGO_MERCHANT_NUMBER)))
    {
      $this->merchantNumber = FormatC::number($this->merchantNumber);

      if($this->merchantNumber === null)
        $this->errors[$this->requestI]['merchantNumber'] = 'Invalid merchant number.';
      else if($this->merchantNumber < $this->merchantNumberMin)
        $this->errors[$this->requestI]['merchantNumber'] = 'Merchant number '.$this->merchantNumber.' must be greater than '.$this->merchantNumberMin.'.';
      else if($this->merchantNumber > $this->merchantNumberMax)
        $this->errors[$this->requestI]['merchantNumber'] = 'Merchant number '.$this->merchantNumber.' must be less than '.$this->merchantNumberMax.'.';

      //$this->merchantNumber = str_pad($this->merchantNumber, $this->merchantNumberStringLength, '0', STR_PAD_LEFT);
    }
    else
      $this->errors[$this->requestI]['merchantNumber'] = 'A merchant number must be supplied.';

    //password
    if(!strlen($this->password))
      $this->errors[$this->requestI]['password'] = 'A password has not been set.';
    else if(!strlen(preg_replace('/[\s]+/', '', $this->password)))
      $this->errors[$this->requestI]['password'] = 'Invalid password.';

    //username
    if(!strlen($this->username))
      $this->errors[$this->requestI]['username'] = 'A username has not been set.';
    else if(!strlen(preg_replace('/[\s]+/', '', $this->username)))
      $this->errors[$this->requestI]['username'] = 'Invalid username.';

    //wsdl url
    if(strtolower($this->wsdlUrlToUse) == 'production')
      $this->wsdlUrl = $this->wsdlUrlProduction;
    else
      $this->wsdlUrl = $this->wsdlUrlTest;

    if($this->errors[$this->requestI])
      return false;

    return true;
  }

  /**
  * This method sends a request to Wells Fargo.
  *
  * @param int transactionCode - The type of transaction taking place
  * @param float amount - The amount between 0.00 and 99,999,999.99 to refund
  * @param int planNumber - The plan number greater than 1000 to refund from
  * @param [string] ticketNumber - A ticket number to associate with the transaction
  * @param [int] authorizationNumber - The authorization number needed to approve the transaction
  * @return bool|array - If no errors: The response as a dictionary Else: false
  */

  protected function sendRequest($transationCode, $amount, $planNumber, $ticketNumber = null, $authorizationNumber = '000000')
  {
    ++$this->requestI;
    $this->unique = substr(\FormatC::number(microtime(true)), -12);

    if(($requestData = $this->prepareRequestData($amount, $planNumber, $ticketNumber, $authorizationNumber)) && $this->requiredDataIsSet())
    {
      extract($requestData);

      $xml =
'<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'.
  '<soapenv:Body>'.
    '<ns1:submitTransaction xmlns:ns1="http://services.webservices.retaildealer.wff.com" soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">'.
      '<trans href="#id0"/>'.
    '</ns1:submitTransaction>'.
    '<multiRef xmlns:ns2="http://model.webservices.retaildealer.wff.com" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" id="id0" soapenc:root="0" soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xsi:type="ns2:Transaction">'.
      '<systemCode xsi:type="xsd:string"/>'.
      '<uuid xsi:type="xsd:string">'.$this->unique.'</uuid>'.
      '<manufacturerNumber xsi:type="xsd:string"/>'.
      '<setupPassword xsi:type="xsd:string">'.$this->password.'</setupPassword>'.
      '<amount xsi:type="xsd:string">'.$amount.'</amount>'.
      '<userName xsi:type="xsd:string">'.$this->username.'</userName>'.
      '<accountNumber xsi:type="xsd:string">'.$this->accountNumber.'</accountNumber>'.
      '<ticketNumber xsi:type="xsd:string"'.($ticketNumber ? '>'.$ticketNumber.'</ticketNumber>' : '/>').
      '<dealerId xsi:type="xsd:string"/>'.
      '<planNumber xsi:type="xsd:string">'.$planNumber.'</planNumber>'.
      '<merchantNumber xsi:type="xsd:string">'.$this->merchantNumber.'</merchantNumber>'.
      '<localeString xsi:type="xsd:string">'.$this->locale.'</localeString>'.
      '<transactionCode xsi:type="xsd:string">'.$transationCode.'</transactionCode>'.
      '<terminalNumber xsi:type="xsd:string">0000</terminalNumber>'.
      '<authorizationNumber xsi:type="xsd:string">'.$authorizationNumber.'</authorizationNumber>'.
    '</multiRef>'.
  '</soapenv:Body>'.
'</soapenv:Envelope>';

      $header =
      [
        'Content-type: text/xml;charset="utf-8"',
        'Accept: text/xml',
        'Cache-Control: no-cache',
        'Pragma: no-cache',
        'SOAPAction: "run"',
        'Content-length: '.strlen($xml),
      ];

      $soap_do = curl_init();

      curl_setopt($soap_do, CURLOPT_URL,            $this->wsdlUrl);
      curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 60);
      curl_setopt($soap_do, CURLOPT_TIMEOUT,        60);
      curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($soap_do, CURLOPT_POST,           true);
      curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $xml);
      curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);

      $this->recordRequest($xml, $header);
      $r = curl_exec($soap_do);

      if($r === false)
      {
        $this->recordResponse(curl_error($soap_do), curl_getinfo($soap_do));
        $this->errors[$this->requestI]['soap'] = curl_error($soap_do);
        curl_close($soap_do);
      }
      else
      {
        $this->recordResponse($r, curl_getinfo($soap_do));
        $contentType = curl_getinfo($soap_do, CURLINFO_CONTENT_TYPE);
        curl_close($soap_do);

        if(preg_match('/text\/xml/i', $contentType))
          return $this->xml2Dictionary($r);

        $this->errors[$this->requestI]['response'] = $r;
      }
    }

    return false; //error as to what needs to be set
  }

  /**
  * This method converts a request from Wells Fargo into a dictionary.
  *
  * @param string response - The response from Wells Fargo
  * @return bool|array - If no errors: The response as a dictionary Else: false
  */

  protected function xml2Dictionary($response)
  {
    $response = array_value
    (
      '[soapenvBody][ns1submitTransactionResponse][submitTransactionReturn]', (json_decode
      (
        json_encode
        (
          simplexml_load_string
          (
            preg_replace('/(<\/?)(\w+):([^>]*>)/', '$1$2$3', $response)
          )
        ),
        true
      ))
    ); //must remove ':' to convert from xml to object

    if(empty($response['faults']))
    {
      switch(array_value('transactionStatus', $response))
      {
        case 'A1': //approved
          return $response;
          break;
        case 'A0': //denied
          /*
          $this->errors[$this->requestI]['returnStatus'] = array_value('transactionMessage', $response);

          if($this->errors[$this->requestI]['returnStatus'] == 'AUTH DENIED' || $this->errors[$this->requestI]['returnStatus'] == 'INVALD FOR MERCH')
            $this->errors[$this->requestI]['returnStatus'] = 'Transaction was declined.  Please verify the account number or use a different one and try again.';
          */

          $this->errors[$this->requestI]['returnStatus'] = 'Transaction was declined.  Please verify the account number or use a different one and try again.';

          break;
        case 'A2':
          $this->errors[$this->requestI]['returnStatus'] = array_value('transactionMessage', $response);
          break;
        case 'A3':
          $this->errors[$this->requestI]['returnStatus'] = array_value('transactionMessage', $response);
          break;
      }

      return false;
    }

    $faults = array_value('[faults][faults]', $response);

    if(isset($faults[0])) //list
      for($i=0, $len=count($faults); $i<$len; ++$i)
        $this->errors[$this->requestI][$faults[$i]['faultString']] = $faults[$i]['faultDetailString'];
    else //dictionary
      $this->errors[$this->requestI][$faults['faultString']] = $faults['faultDetailString'];

    return false;
  }
}

/**
* This function retreives a value from the supplied array.
*
* @param string key - The array key to find
* @param array array - The array to retreive the value from
* @param mixed default - The value to return should the key not be found
* @return mixed
*/

function array_value($keys, $array, $default = null)
{
  //initialize variables
  $value = $array;

  if(array_test($array, 2, 'array_value()'))
  {
    //split $keys into array
    $keys = explode('][', trim($keys, '[]'));

    for($i=0, $len=count($keys); $i<$len; ++$i)
      if(array_key_exists($keys[$i], $value))
        $value = $value[$keys[$i]];
      else
        return $default;
  }
  else
    return $default;

  return $value;
}

/**
* This method tests if a variable is an array and throws a warning if not.
*
* @param mixed var - The variable to convert
* @param [bool=false] purge - Remove NULL values
* @return array - The converted array
*/

function array_test($array, $parameter, $function)
{
  if(!is_array($array))
  {
    trigger_error($function.' expects parameter '.$parameter.' to be an array, '.gettype($array).' given', E_USER_WARNING);
    return false;
  }

  return true;
}
