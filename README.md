# Coinbase PHP Client Library

An easy way to buy, send, and accept [bitcoin](http://en.wikipedia.org/wiki/Bitcoin) through the [Coinbase API](https://coinbase.com/docs/api/overview).

This library supports both the [API key authentication method](https://coinbase.com/docs/api/overview) and OAuth. The below examples use an API key - for instructions on how to use OAuth, see [OAuth Authentication](#oauth-authentication).

## Installation

Obtain the latest version of the Coinbase PHP library with:

    git clone https://github.com/coinbase/coinbase-php

Then, add the following to your PHP script:

    require_once("/path/to/coinbase-php/lib/Coinbase.php");

## Usage

Start by [enabling an API Key on your account](https://coinbase.com/account/integrations).

Next, create an instance of the client using the `Coinbase::withApiKey` method:

```php
$coinbase = Coinbase::withApiKey($_ENV['COINBASE_API_KEY'], $_ENV['COINBASE_API_SECRET'])
```

Notice here that we did not hard code the API key into our codebase, but set it in an environment variable instead.  This is just one example, but keeping your credentials separate from your code base is a good [security practice](https://coinbase.com/docs/api/overview#security).

Now you can call methods on `$coinbase` similar to the ones described in the [API reference](https://coinbase.com/api/doc).  For example:

```php
$balance = $coinbase->getBalance();
echo "Balance is " . $balance . " BTC";
```
  
Currency amounts are returned as Strings. To avoid precision errors, use the [PHP arbitrary precision math functions ](http://www.php.net/manual/en/ref.bc.php) to work with money amounts.

A working API key example is available in `example/ApiKeyExample.php`.

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

You can also send money in a number of currencies (see `getCurrencies()`) using the fifth parameter.  The amount will be automatically converted to the correct BTC amount using the current exchange rate.

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

### List your current transactions

Sorted in descending order by timestamp, 30 per page.  You can pass an integer as the first param to page through results, for example `$coinbase->getTransactions(2)`.

```php
$response = $coinbase->getTransactions();
echo $response->current_page;
// '1'
echo $response->num_pages;
// '2'
echo $response->transactions[0]->id;
// '5018f833f8182b129c00002f'
```

Transactions will always have an `id` attribute which is the primary way to identity them through the Coinbase api.  They will also have a `hsh` (bitcoin hash) attribute once they've been broadcast to the network (usually within a few seconds).

### Check bitcoin prices

Check the buy or sell price by passing a `quantity` of bitcoin that you'd like to buy or sell.  This price includes Coinbase's fee of 1% and the bank transfer fee of $0.15.

```php
echo $coinbase->getBuyPrice('1');
// '125.31'
echo $coinbase->getSellPrice('1');
// '122.41'
```

### Buy or sell bitcoin

Buying and selling bitcoin requires you to [link and verify a bank account](https://coinbase.com/payment_methods) through the web interface first.

Then you can call `buy` or `sell` and pass a `$quantity` of bitcoin you want to buy.

On a buy, we'll debit your bank account and the bitcoin will arrive in your Coinbase account four business days later (this is shown as the `payout_date` below).  This is how long it takes for the bank transfer to complete and verify, although we're working on shortening this window. In some cases, we may not be able to guarantee a price, and buy requests will fail. In that case, set the second parameter (`$agreeBtcAmountVaries`) to true in order to purchase bitcoin at the future market price when your money arrives.

On a sell we'll credit your bank account in a similar way and it will arrive within two business days.

```php
$response = $coinbase->buy('1.0');
echo $response->transfer->code;
// '6H7GYLXZ'
echo $response->transfer->btc->amount;
// '1.00000000'
echo $response->transfer->total->amount;
// '$17.95'
echo $response->transfer->payout_date;
// '2013-02-01T18:00:00-08:00' (ISO 8601 format - can be parsed with the strtotime() function)
```

```php
$response = $coinbase->sell('1.0');
echo $response->transfer->code;
// 'RD2OC8AL'
echo $response->transfer->btc->amount;
// '1.00000000'
echo $response->transfer->total->amount;
// '$17.95'
echo $response->transfer->payout_date;
// '2013-02-01T18:00:00-08:00' (ISO 8601 format - can be parsed with the strtotime() function)
```

### Create a payment button

This will create the code for a payment button (and modal window) that you can use to accept bitcoin on your website.  You can read [more about payment buttons here and try a demo](https://coinbase.com/docs/merchant_tools/payment_buttons).

The method signature is `public function createButton($name, $price, $currency, $custom=null, $options=array())`.  The `custom` param will get passed through in [callbacks](https://coinbase.com/docs/merchant_tools/callbacks) to your site.  The list of valid `options` [are described here](https://coinbase.com/api/doc/1.0/buttons/create.html).

```php
$response = $coinbase->createButton("Your Order #1234", "42.95", "EUR", "my custom tracking code for this order", array(
            "description" => "1 widget at €42.95"
        ));
echo $response->button->code;
// '93865b9cae83706ae59220c013bc0afd'
echo $response->embedHtml;
// '<div class=\"coinbase-button\" data-code=\"93865b9cae83706ae59220c013bc0afd\"></div><script src=\"https://coinbase.com/assets/button.js\" type=\"text/javascript\"></script>'
```

### Exchange rates and currency utilties

You can fetch a list of all supported currencies and ISO codes with the `getCurrencies()` method.

```php
$currencies = $coinbase->getCurrencies();
echo $currencies[0]->name;
// 'Afghan Afghani (AFN)'
```

`getExchangeRate()` will return a list of exchange rates. Pass two parameters to get a single exchange rate.

```php
$rates = $coinbase->getExchangeRate();
echo $rates->btc_to_cad;
// '117.13892'
echo $coinbase->getExchangeRate('btc', 'cad');
// '117.13892'
```

### Create a new user

```php
$response = $coinbase->createUser("newuser@example.com", "some password");
echo $response->user->email;
// 'newuser@example.com'
echo $response->user->receive_address;
// 'mpJKwdmJKYjiyfNo26eRp4j6qGwuUUnw9x'
```

A receive address is returned also in case you need to send the new user a payment right away.

### Get autocomplete contacts

This will return a list of contacts the user has previously sent to or received from. Useful for auto completion. By default, 30 contacts are returned at a time; use the `$page` and `$limit` parameters to adjust how pagination works.

```php
$response = $coinbase->getContacts("exa");
echo implode(', ', $response->contacts);
// 'user1@example.com, user2@example.com'
```

## Adding new methods

You can see a [list of method calls here](https://github.com/coinbase/coinbase-php/blob/master/lib/Coinbase/Coinbase.php) and how they are implemented.  They are a wrapper around the [Coinbase JSON API](https://coinbase.com/api/doc).

If there are any methods listed in the [API Reference](https://coinbase.com/api/doc) that don't have an explicit function name in the library, you can also call `get`, `post`, `put`, or `delete` with a `$path` and optional `$params` array for a quick implementation.  The raw JSON object will be returned. For example:

```php
var_dump($coinbase->get('/account/balance'));
// object(stdClass)#4 (2) {
//   ["amount"]=>
//   string(10) "0.56902981"
//   ["currency"]=>
//   string(3) "BTC"
// }
```

Or feel free to add a new wrapper method and submit a pull request.

## OAuth Authentication

To authenticate with OAuth, first create an OAuth application at https://coinbase.com/oauth/applications.
When a user wishes to connect their Coinbase account, redirect them to a URL created with `Coinbase_OAuth::createAuthorizeUrl`:

```php
$coinbaseOauth = new Coinbase_OAuth($_CLIENT_ID, $_CLIENT_SECRET, $_REDIRECT_URL);
header("Location: " . $coinbaseOauth->createAuthorizeUrl("all"));
```

After the user has authorized your application, they will be redirected back to the redirect URL specified above. A `code` parameter will be included - pass this into `getTokens` to receive a set of tokens:

```php
$tokens = $coinbaseOauth->getTokens($_GET['code']);
```

Store these tokens safely, and use them to make Coinbase API requests in the future. For example:

```php
$coinbase = Coinbase::withOauth($coinbaseOauth, $tokens);
$coinbase->getBalance();
```

A full example implementation is available in the `example` directory.

## Simple API Key Authentication

If you're still using the deprecated Simple API keys, create a Coinbase object like so:

```php
$coinbase = Coinbase::withSimpleApiKey($simple_api);
```

## Security notes

If someone gains access to your API Key they will have complete control of your Coinbase account.  This includes the abillity to send all of your bitcoins elsewhere.

For this reason, API access is disabled on all Coinbase accounts by default.  If you decide to enable API key access you should take precautions to store your API key securely in your application.  How to do this is application specific, but it's something you should [research](http://programmers.stackexchange.com/questions/65601/is-it-smart-to-store-application-keys-ids-etc-directly-inside-an-application) if you have never done this before.

## Testing

If you'd like to contribute code or modify this library, you can run the test suite by executing `/path/to/coinbase-php/test/Coinbase.php` in a web browser or on the command line with `php`.
