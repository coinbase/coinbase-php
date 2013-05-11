# Coinbase PHP Client Library

An easy way to buy, send, and accept [bitcoin](http://en.wikipedia.org/wiki/Bitcoin) through the [Coinbase API](https://coinbase.com/docs/api/overview).

This library only supports the [API key authentication method](https://coinbase.com/docs/api/overview). OAuth2 support is not yet implemented.

## Installation

Obtain the latest version of the Coinbase PHP library with:

    git clone https://github.com/coinbase/coinbase-php

Then, add the following to your PHP script:

    require_once("/path/to/coinbase-php/lib/Coinbase.php");

## Usage

Start by [enabling an API Key on your account](https://coinbase.com/account/integrations).

Next, create an instance of the client and pass it your API Key as the first (and only) parameter.

```php
$coinbase = new Coinbase($_ENV['COINBASE_API_KEY'])
```

Notice here that we did not hard code the API key into our codebase, but set it in an environment variable instead.  This is just one example, but keeping your credentials separate from your code base is a good [security practice](https://coinbase.com/docs/api/overview#security).

Now you can call methods on `$coinbase` similar to the ones described in the [API reference](https://coinbase.com/api/doc).  For example:

```php
$balance = $coinbase->getBalance();
echo "Balance is " . $balance . " BTC";
```
  
Currency amounts are returned as Strings. To avoid precision errors, use the [PHP arbitrary precision math functions ](http://www.php.net/manual/en/ref.bc.php) to work with money amounts.

## Examples

### Check your balance

```php
echo $coinbase->getBalance() . " BTC";
// '200.123 BTC'
```

### Send bitcoin

`public function sendMoney($to, $amount, $notes=null, $userFee=null, $amountCurrency=null)`

```php
$response = $coinbase->sendMoney("user@example.com", "2");
echo $response->success ? 'true' : 'false';
// 'true'
echo $response->transaction->status;
// 'pending'
echo $response->transaction->id;
// '518d8567ed3ddcd4fd000034'
```

The first parameter can also be a bitcoin address and the third parameter can be a note or description of the transaction.  Descriptions are only visible on Coinbase (not on the general bitcoin network).

```php
$response = $coinbase->sendMoney("mpJKwdmJKYjiyfNo26eRp4j6qGwuUUnw9x", "0.1", "thanks for the coffee!");
echo $response->transaction->notes;
// 'thanks for the coffee!'
```

You can also send money in [a number of currencies](https://github.com/coinbase/coinbase-ruby/blob/master/supported_currencies.json) using the fifth parameter.  The amount will be automatically converted to the correct BTC amount using the current exchange rate.

```php
$response = $coinbase->sendMoney("user@example.com", "2", null, null, "CAD");
echo $response->transaction->amount->amount;
// '0.0169'
```

### Request bitcoin

This will send an email to the recipient, requesting payment, and give them an easy way to pay.

```php
$response = $coinbase->requestMoney('client@example.com', 50, "contractor hours in January (website redesign for 50 BTC)");
echo $response->transaction->request ? 'true' : 'false';
// 'true'
echo $response->transaction->id;
// '501a3554f8182b2754000003'

$response = $coinbase->resendRequest('501a3554f8182b2754000003');
echo $response->success ? 'true' : 'false';
// 'true'

$response = $coinbase->cancelRequest('501a3554f8182b2754000003');
echo $response->success ? 'true' : 'false';
// 'true'

// From the other account:
$response = $coinbase->completeRequest('501a3554f8182b2754000003');
echo $response->success ? 'true' : 'false';
// 'true'
```
