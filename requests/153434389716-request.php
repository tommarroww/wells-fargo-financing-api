Content-type: text/xml;charset="utf-8"
Accept: text/xml
Cache-Control: no-cache
Pragma: no-cache
SOAPAction: "run"
Content-length: 1480

<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<soapenv:Body>
<ns1:submitTransaction xmlns:ns1="http://services.webservices.retaildealer.wff.com" soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<trans href="#id0"/>
</ns1:submitTransaction>
<multiRef xmlns:ns2="http://model.webservices.retaildealer.wff.com" xmlns:soapenc="http://schemas.xmlsoap.org/soap/encoding/" id="id0" soapenc:root="0" soapenv:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xsi:type="ns2:Transaction">
<systemCode xsi:type="xsd:string"/>
<uuid xsi:type="xsd:string">153434389716</uuid>
<manufacturerNumber xsi:type="xsd:string"/>
<setupPassword xsi:type="xsd:string">863cbceb</setupPassword>
<amount xsi:type="xsd:string">542.38</amount>
<userName xsi:type="xsd:string">fred</userName>
<accountNumber xsi:type="xsd:string">9999999999999991</accountNumber>
<ticketNumber xsi:type="xsd:string">O180810-214</ticketNumber>
<dealerId xsi:type="xsd:string"/>
<planNumber xsi:type="xsd:string">9999</planNumber>
<merchantNumber xsi:type="xsd:string">100000000000001</merchantNumber>
<localeString xsi:type="xsd:string">en_US</localeString>
<transactionCode xsi:type="xsd:string">1</transactionCode>
<terminalNumber xsi:type="xsd:string">0000</terminalNumber>
<authorizationNumber xsi:type="xsd:string">000000</authorizationNumber>
</multiRef>
</soapenv:Body>
</soapenv:Envelope>