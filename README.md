# Wells Fargo Financing API (wells-fargo-financing-api)
This API manages transactions (via SOAP) between an online merchant and Wells Fargo Retail Services.

The API can manage these types of transactions:
- Charges
- Refunds

## Using the API
### Initialization
~~~~
use WellsFargo as WF;

define('SERVER_ROOT', '/home/user/littletzar/');
define('WELLS_FARGO_MERCHANT_NUMBER', '1234567890');
define('WELLS_FARGO_PASSWORD', 'pi47*kl');
define('WELLS_FARGO_USERNAME', 'm67543');

require SERVER_ROOT.'addons/wells-fargo/wells-fargo.class.php';

$WellsFargo = new WF\WellsFargoC(
[
  'accountNumber' => $accountNumber,
  'merchantNumber' => WELLS_FARGO_MERCHANT_NUMBER,
  'password' => WELLS_FARGO_PASSWORD,
  'username' => WELLS_FARGO_USERNAME,
]);
~~~~

It works best to define the **Merchant Number**, **Username**, and **Password** in the configuration file of your application.

**$accountNumber** is typically supplied the customer when a purchase is made.

These properties can be set or changed later as follows:

~~~~
use WellsFargo as WF;

define('SERVER_ROOT', '/home/user/littletzar/');
define('WELLS_FARGO_MERCHANT_NUMBER', '1234567890');
define('WELLS_FARGO_PASSWORD', 'pi47*kl');
define('WELLS_FARGO_USERNAME', 'm67543');

require SERVER_ROOT.'addons/wells-fargo/wells-fargo.class.php';

$WellsFargo = new \WellsFargoC();

$WellsFargo->setter(
[
  'accountNumber' => $accountNumber,
  'merchantNumber' => WELLS_FARGO_MERCHANT_NUMBER,
  'password' => WELLS_FARGO_PASSWORD,
  'username' => WELLS_FARGO_USERNAME,
]);
~~~~

### Charge & Refund
~~~~
if(($r = $WellsFargo->charge(542.38, 9999, 'O180810-214')) === false)
  var_dump($WellsFargo->getErrors());
else
{
  *...code here*
}
~~~~

~~~~
if(($r = $WellsFargo->refund(542.38, 9999, 'O180810-214')) === false)
  var_dump($WellsFargo->getErrors());
else
{
  *...code here*
}
~~~~

Both methods take arguments in this order:
1. Amount between 0.00 and 99,999,999.99
2. Plan Number between 1000 and 9999
3. Ticket Number <= 12 characters (optional - Best used for passing the order number)

If the transaction fails for any reason, ***false*** will be returned.  To retrieve the errors call ***WellsFargo->getErrors()***.  If more than one transaction has been sent, the errors for the most recent transaction will be returned.  To retrieve errors from other transactions, pass an integer >= 0.  The method will return a dictionary of all the errors that occurred.  For example:

~~~~
array(2) {
  ["ticketNumber"]=>
  string(35) "Ticket number must be less than 12 characters.",
  ["returnStatus"]=>
  string(11) "AUTH DENIED"
}
~~~~

Successful transaction will return a dictionary of the the results.  For example:

~~~~
array(11) {
  ["accountNumber"]=>
  string(16) "9999999999999991"
  ["amount"]=>
  string(6) "542.38"
  ["authorizationNumber"]=>
  string(6) "000000"
  ["disclosure"]=>
  string(131) "(DEMO) REGULAR TERMS WITH REGULAR PAYMENTS. THE REGULAR RATE IS 27.99%. THIS APR WILL VARY WITH THE MARKET BASED ON THE PRIME RATE."
  ["faults"]=>
  array(0) {
  }
  ["planNumber"]=>
  string(4) "9999"
  ["ticketNumber"]=>
  string(11) "O180810-214"
  ["transactionCode"]=>
  string(1) "1"
  ["transactionMessage"]=>
  string(16) "APPROVED: 000000"
  ["transactionStatus"]=>
  string(2) "A1"
  ["uuid"]=>
  string(12) "111222333444"
}
~~~~

### Properties
These properties may be set or changed at any time:
- accountNumber
- dealerId
- locale
  - 'en_CA'
  - 'en_US' (default)
  - 'fr_CA'
- merchantNumber
- password
- username
- wsdlUrlToUse
  - 'production'
  - 'test' (default)
  - May also be set by defining the global: define('WELLS_FARGO_WSDL_URL_TO_USE' 'test')
