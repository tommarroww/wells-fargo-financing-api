array (
  'url' => 'https://retailservices-uat.wellsfargo.com/services/SubmitTransactionService',
  'content_type' => 'text/xml;charset=utf-8',
  'http_code' => 200,
  'header_size' => 2255,
  'request_size' => 257,
  'filetime' => -1,
  'ssl_verify_result' => 0,
  'redirect_count' => 0,
  'total_time' => 3.165455000000000129745103549794293940067291259765625,
  'namelookup_time' => 2.69999999999999989899572561125040692786569707095623016357421875E-5,
  'connect_time' => 0.044190000000000000113242748511765967123210430145263671875,
  'pretransfer_time' => 0.151216999999999990311749797911033965647220611572265625,
  'size_upload' => 1493,
  'size_download' => 898,
  'speed_download' => 283,
  'speed_upload' => 471,
  'download_content_length' => -1,
  'upload_content_length' => 1493,
  'starttransfer_time' => 0.2102749999999999896971303314785473048686981201171875,
  'redirect_time' => 0,
  'redirect_url' => '',
  'primary_ip' => '159.45.201.67',
  'certinfo' => 
  array (
  ),
  'primary_port' => 443,
  'local_ip' => '138.128.190.250',
  'local_port' => 40888,
)

<?xml version="1.0" encoding="utf-8"?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<soapenv:Body>
<ns1:submitTransactionResponse soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:ns1="http://services.webservices.retaildealer.wff.com">
<submitTransactionReturn>
<accountNumber>5774421715085362</accountNumber>
<amount>542.38</amount>
<authorizationNumber>000000</authorizationNumber>
<disclosure>
</disclosure>
<faults xsi:nil="true"/>
<planNumber>9999</planNumber>
<ticketNumber>O180810-214</ticketNumber>
<transactionCode>1</transactionCode>
<transactionMessage>INVALD FOR MERCH</transactionMessage>
<transactionStatus>A0</transactionStatus>
<uuid>153434474048</uuid>
</submitTransactionReturn>
</ns1:submitTransactionResponse>
</soapenv:Body>
</soapenv:Envelope>