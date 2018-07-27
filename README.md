# Coinbase Wallet PHP Library

[![Build Status](https://travis-ci.org/coinbase/coinbase-php.svg)](https://travis-ci.org/coinbase/coinbase-php)
[![Latest Stable Version](https://poser.pugx.org/coinbase/coinbase/v/stable)](https://packagist.org/packages/coinbase/coinbase)
[![Total Downloads](https://poser.pugx.org/coinbase/coinbase/downloads)](https://packagist.org/packages/coinbase/coinbase)
[![Latest Unstable Version](https://poser.pugx.org/coinbase/coinbase/v/unstable)](https://packagist.org/packages/coinbase/coinbase)
[![License](https://poser.pugx.org/coinbase/coinbase/license)](https://packagist.org/packages/coinbase/coinbase)

This is the official client library for the [Coinbase Wallet API v2][1]. We
provide an intuitive, stable interface to integrate Coinbase Wallet into your
PHP project.

_Important:_ As this library is targeted for newer API v2, it requires v2
permissions (i.e. `wallet:accounts:read`). If you're still using v1, please use
the [older version][2] of this library.

## Installation

Install the library using Composer. Please read the [Composer Documentation](https://getcomposer.org/doc/01-basic-usage.md) if you are unfamiliar with Composer or dependency managers in general.

```json
"require": {
    "coinbase/coinbase": "~2.0"
}
```

## Authentication

### API Key

Use an API key and secret to access your own Coinbase account.

```php
use Coinbase\Wallet\Client;
use Coinbase\Wallet\Configuration;

$configuration = Configuration::apiKey($apiKey, $apiSecret);
$client = Client::create($configuration);
```

### OAuth2

Use OAuth2 authentication to access a user's account other than your own. This
library does not handle the handshake process, and assumes you have an access
token when it's initialized. You can handle the handshake process using an
[OAuth2 client][5] such as [league/oauth2-client][6].

```php
use Coinbase\Wallet\Client;
use Coinbase\Wallet\Configuration;

// with a refresh token
$configuration = Configuration::oauth($accessToken, $refreshToken);

// without a refresh token
$configuration = Configuration::oauth($accessToken);

$client = Client::create($configuration);
```

### Two factor authentication

The send money endpoint requires a 2FA token in certain situations (read more
[here][3]). A specific exception is thrown when this is required.

```php
use Coinbase\Wallet\Enum\Param;
use Coinbase\Wallet\Exception\TwoFactorRequiredException;
use Coinbase\Wallet\Resource\Transaction;

$transaction = Transaction::send([
    'toEmail' => 'test@test.com',
    'bitcoinAmount' => 1
]);

$account = $client->getPrimaryAccount();
try {
    $client->createAccountTransaction($account, $transaction);
} catch (TwoFactorRequiredException $e) {
    // show 2FA dialog to user and collect 2FA token

    // retry call with token
    $client->createAccountTransaction($account, $transaction, [
        Param::TWO_FACTOR_TOKEN => '123456',
    ]);
}
```

### Pagination

Several endpoints are [paginated][4]. By default, the library will only fetch
the first page of data for a given request. You can easily load more than just
the first page of results.

```php
$transactions = $client->getAccountTransactions($account);
while ($transactions->hasNextPage()) {
    $client->loadNextTransactions($transactions);
}
```

You can also use the `fetch_all` parameter to have the library issue all the
necessary requests to load the complete collection.

```php
use Coinbase\Wallet\Enum\Param;

$transactions = $client->getAccountTransactions($account, [
    Param::FETCH_ALL => true,
]);
```

### Warnings

It's prudent to be conscious of warnings. The library will log all warnings to a
standard PSR-3 logger if one is configured.

```php
use Coinbase\Wallet\Client;
use Coinbase\Wallet\Configuration;

$configuration = Configuration::apiKey($apiKey, $apiSecret);
$configuration->setLogger($logger);
$client = Client::create($configuration);
```

### Resource references

In some cases the API will return resource references in place of expanded
resource objects. These references can be expanded by refreshing them.

```php
$deposit = $this->client->getAccountDeposit($account, $depositId);
$transaction = $deposit->getTransaction();
if (!$transaction->isExpanded()) {
    $this->client->refreshTransaction($transaction);
}
```

You can also request that the API return an expanded resource in the initial
request by using the `expand` parameter.

```php
use Coinbase\Wallet\Enum\Param;

$deposit = $this->client->getAccountDeposit($account, $depositId, [
    Param::EXPAND = ['transaction'],
]);
```

Resource references can be used when creating new resources, avoiding the
overhead of requesting a resource from the API.

```php
use Coinbase\Wallet\Resource\Deposit;
use Coinbase\Wallet\Resource\PaymentMethod;

$deposit = new Deposit([
    'paymentMethod' => PaymentMethod::reference($paymentMethodId)
]);

// or use the convenience method
$deposit = new Deposit([
    'paymentMethodId' => $paymentMethodId
]);
```

### Responses

There are multiple ways to access raw response data. First, each resource
object has a `getRawData()` method which you can use to access any field that
are not mapped to the object properties.

```php
$data = $deposit->getRawData();
```

Raw data from the last HTTP response is also available on the client object.

```php
$data = $client->decodeLastResponse();
```

### Active record methods

The library includes support for active record methods on resource objects. You
must enable this functionality when bootstrapping your application.

```php
$client->enableActiveRecord();
```

Once enabled, you can call active record methods on resource objects.

```php
use Coinbase\Wallet\Enum\Param;

$transactions = $account->getTransactions([
    Param::FETCH_ALL => true,
]);
```

## Usage

This is not intended to provide complete documentation of the API. For more
detail, please refer to the
[official documentation](https://developers.coinbase.com/api/v2).

### [Market Data](https://developers.coinbase.com/api/v2#data-api)

**List supported native currencies**

```php
$currencies = $client->getCurrencies();
```

**List exchange rates**

```php
$rates = $client->getExchangeRates();
```

**Buy price**

```php
$buyPrice = $client->getBuyPrice('BTC-USD');
```

**Sell price**

```php
$sellPrice = $client->getSellPrice('BTC-USD');
```

**Spot price**

```php
$spotPrice = $client->getSpotPrice('BTC-USD');
```

**Current server time**

```php
$time = $client->getTime();
```

### [Users](https://developers.coinbase.com/api/v2#users)

**Get authorization info**

```php
$auth = $client->getCurrentAuthorization();
```

**Lookup user info**

```php
$user = $client->getUser($userId);
```

**Get current user**

```php
$user = $client->getCurrentUser();
```

**Update current user**

```php
$user->setName('New Name');
$client->updateCurrentUser($user);
```

### [Accounts](https://developers.coinbase.com/api/v2#accounts)

**List all accounts**

```php
$accounts = $client->getAccounts();
```

**List account details**

```php
$account = $client->getAccount($accountId);
```

**List primary account details**

```php
$account = $client->getPrimaryAccount();
```

**Set account as primary**

```php
$client->setPrimaryAccount($account);
```

**Create a new bitcoin account**

```php
use Coinbase\Wallet\Resource\Account;

$account = new Account([
    'name' => 'New Account'
]);
$client->createAccount($account);
```

**Update an account**

```php
$account->setName('New Account Name');
$client->updateAccount($account):
```

**Delete an account**

```php
$client->deleteAccount($account);
```

### [Addresses](https://developers.coinbase.com/api/v2#addresses)

**List receive addresses for account**

```php
$addresses = $client->getAccountAddresses($account);
```

**Get receive address info**

```php
$address = $client->getAccountAddress($account, $addressId);
```

**List transactions for address**

```php
$transactions = $client->getAddressTransactions($address);
```

**Create a new receive address**

```php
use Coinbase\Wallet\Resource\Address;

$address = new Address([
    'name' => 'New Address'
]);
$client->createAccountAddress($account, $address);
```

### [Transactions](https://developers.coinbase.com/api/v2#transactions)

**List transactions**

```php
$transactions = $client->getAccountTransactions($account);
```

**Get transaction info**

```php
$transaction = $client->getAccountTransaction($account, $transactionId);
```

**Send funds**

```php
use Coinbase\Wallet\Enum\CurrencyCode;
use Coinbase\Wallet\Resource\Transaction;
use Coinbase\Wallet\Value\Money;

$transaction = Transaction::send([
    'toBitcoinAddress' => 'ADDRESS',
    'amount'           => new Money(5, CurrencyCode::USD),
    'description'      => 'Your first bitcoin!',
    'fee'              => '0.0001' // only required for transactions under BTC0.0001
]);

try { $client->createAccountTransaction($account, $transaction); }
catch(Exception $e) {
     echo $e->getMessage(); 
}
```

**Transfer funds to a new account**

```php
use Coinbase\Wallet\Resource\Transaction;
use Coinbase\Wallet\Resource\Account;

$fromAccount = Account::reference($accountId);

$toAccount = new Account([
    'name' => 'New Account'
]);
$client->createAccount($toAccount);

$transaction = Transaction::transfer([
    'to'            => $toAccount,
    'bitcoinAmount' => 1,
    'description'   => 'Your first bitcoin!'
]);

$client->createAccountTransaction($fromAccount, $transaction);
```

**Request funds**

```php
use Coinbase\Wallet\Enum\CurrencyCode;
use Coinbase\Wallet\Resource\Transaction;
use Coinbase\Wallet\Value\Money;

$transaction = Transaction::request([
    'amount'      => new Money(8, CurrencyCode::USD),
    'description' => 'Burrito'
]);

$client->createAccountTransaction($transaction);
```

**Resend request**

```php
$account->resendTransaction($transaction);
```

**Cancel request**

```php
$account->cancelTransaction($transaction);
```

**Fulfill request**

```php
$account->completeTransaction($transaction);
```

### [Buys](https://developers.coinbase.com/api/v2#buys)

**List buys**

```php
$buys = $client->getAccountBuys($account);
```

**Get buy info**

```php
$buy = $client->getAccountBuy($account, $buyId);
```

**Buy bitcoins**

```php
use Coinbase\Wallet\Resource\Buy;

$buy = new Buy([
    'bitcoinAmount' => 1
]);

$client->createAccountBuy($account, $buy);
```

**Commit a buy**

You only need to do this if you pass `commit=false` when you create the buy.

```php
use Coinbase\Wallet\Enum\Param;

$client->createAccountBuy($account, $buy, [Param::COMMIT => false]);
$client->commitBuy($buy);
```

### [Sells](https://developers.coinbase.com/api/v2#sells)

**List sells**

```php
$sells = $client->getAccountSells($account);
```

**Get sell info**

```php
$sell = $client->getAccountSell($account, $sellId);
```

**Sell bitcoins**

```php
use Coinbase\Wallet\Resource\Sell;

$sell = new Sell([
    'bitcoinAmount' => 1
]);

$client->createAccountSell($account, $sell);
```

**Commit a sell**

You only need to do this if you pass `commit=false` when you create the sell.

```php
use Coinbase\Wallet\Enum\Param;

$client->createAccountSell($account, $sell, [Param::COMMIT => false]);
$client->commitSell($sell);
```

### [Deposit](https://developers.coinbase.com/api/v2#deposits)

**List deposits**

```php
$deposits = $client->getAccountDeposits($account);
```

**Get deposit info**

```php
$deposit = $client->getAccountDeposit($account, $depositId);
```

**Deposit funds**

```php
use Coinbase\Wallet\Enum\CurrencyCode;
use Coinbase\Wallet\Resource\Deposit;
use Coinbase\Wallet\Value\Money;

$deposit = new Deposit([
    'amount' => new Money(10, CurrencyCode::USD)
]);

$client->createAccountDeposit($account, $deposit);
```

**Commit a deposit**

You only need to do this if you pass `commit=false` when you create the deposit.

```php
use Coinbase\Wallet\Enum\Param;

$client->createAccountDeposit($account, $deposit, [Param::COMMIT => false]);
$client->commitDeposit($deposit);
```

### [Withdrawals](https://developers.coinbase.com/api/v2#withdrawals)

**List withdrawals**

```php
$withdrawals = $client->getAccountWithdrawals($account);
```

**Get withdrawal**

```php
$withdrawal = $client->getAccountWithdrawal($account, $withdrawalId);
```

**Withdraw funds**

```php
use Coinbase\Wallet\Enum\CurrencyCode;
use Coinbase\Wallet\Resource\Withdrawal;
use Coinbase\Wallet\Value\Money;

$withdrawal = new Withdrawal([
    'amount' => new Money(10, CurrencyCode::USD)
]);

$client->createAccountWithdrawal($account, $withdrawal);
```

**Commit a withdrawal**

You only need to do this if you pass `commit=true` when you call the withdrawal method.

```php
use Coinbase\Wallet\Enum\Param;

$client->createAccountWithdrawal($account, $withdrawal, [Param::COMMIT => false]);
$client->commitWithdrawal($withdrawal);
```

### [Payment Methods](https://developers.coinbase.com/api/v2#payment-methods)

**List payment methods**

```php
$paymentMethods = $client->getPaymentMethods();
```

**Get payment method**

```php
$paymentMethod = $client->getPaymentMethod($paymentMethodId);
```

### [Merchants](https://developers.coinbase.com/api/v2#merchants)

#### Get merchant

```php
$merchant = $client->getMerchant($merchantId);
```

### [Orders](https://developers.coinbase.com/api/v2#orders)

#### List orders

```php
$orders = $client->getOrders();
```

#### Get order

```php
$order = $client->getOrder($orderId);
```

#### Create order

```php
use Coinbase\Wallet\Resource\Order;
use Coinbase\Wallet\Value\Money;

$order = new Order([
    'name' => 'Order #1234',
    'amount' => Money::btc(1)
]);

$client->createOrder($order);
```

#### Refund order

```php
use Coinbase\Wallet\Enum\CurrencyCode;

$client->refundOrder($order, CurrencyCode::BTC);
```

### Checkouts

#### List checkouts

```php
$checkouts = $client->getCheckouts();
```

#### Create checkout

```php
use Coinbase\Wallet\Resource\Checkout;

$params = array(
    'name'               => 'My Order',
    'amount'             => new Money(100, 'USD'),
    'metadata'           => array( 'order_id' => $custom_order_id )
);

$checkout = new Checkout($params);
$client->createCheckout($checkout);
$code = $checkout->getEmbedCode();
$redirect_url = "https://www.coinbase.com/checkouts/$code";
```

#### Get checkout

```php
$checkout = $client->getCheckout($checkoutId);
```

#### Get checkout's orders

```php
$orders = $client->getCheckoutOrders($checkout);
```

#### Create order for checkout

```php
$order = $client->createNewCheckoutOrder($checkout);
```

### [Notifications webhook and verification](https://developers.coinbase.com/docs/wallet/notifications)

```php
$raw_body = file_get_contents('php://input');
$signature = $_SERVER['HTTP_CB_SIGNATURE'];
$authenticity = $client->verifyCallback($raw_body, $signature); // boolean
```

## Contributing and testing

The test suite is built using PHPUnit. Run the suite of unit tests by running
the `phpunit` command.

```
phpunit
```

There is also a collection of integration tests that issues real requests to the
API and inspects the resulting objects. To run these tests, you must copy
`phpunit.xml.dist` to `phpunit.xml`, provide values for the `CB_API_KEY` and
`CB_API_SECRET` variables, and specify the `integration` group when running the
test suite.

```
phpunit --group integration
```

[1]: https://developers.coinbase.com/api/v2
[2]: https://packagist.org/packages/coinbase/coinbase
[3]: https://developers.coinbase.com/docs/wallet/coinbase-connect#two-factor-authentication
[4]: https://developers.coinbase.com/api/v2#pagination
[5]: https://packagist.org/search/?q=oauth2%20client
[6]: https://packagist.org/packages/league/oauth2-client
