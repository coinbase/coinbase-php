<?php

namespace Coinbase\Wallet;

use Coinbase\Wallet\ActiveRecord\ActiveRecordContext;
use Coinbase\Wallet\Enum\Param;
use Coinbase\Wallet\Resource\Account;
use Coinbase\Wallet\Resource\Address;
use Coinbase\Wallet\Resource\Buy;
use Coinbase\Wallet\Resource\Checkout;
use Coinbase\Wallet\Resource\CurrentUser;
use Coinbase\Wallet\Resource\Deposit;
use Coinbase\Wallet\Resource\Merchant;
use Coinbase\Wallet\Resource\Order;
use Coinbase\Wallet\Resource\PaymentMethod;
use Coinbase\Wallet\Resource\Resource;
use Coinbase\Wallet\Resource\ResourceCollection;
use Coinbase\Wallet\Resource\Sell;
use Coinbase\Wallet\Resource\Transaction;
use Coinbase\Wallet\Resource\User;
use Coinbase\Wallet\Resource\Withdrawal;
use Coinbase\Wallet\Resource\Notification;

/**
 * A client for interacting with the Coinbase API.
 *
 * All methods marked as supporting pagination parameters support the following
 * parameters:
 *
 *  * limit (integer)
 *  * order (string)
 *  * starting_after (string)
 *  * ending_before (string)
 *  * fetch_all (Boolean)
 *
 * @link https://developers.coinbase.com/api/v2
 */
class Client
{
    const VERSION = '2.2.0';

    private $http;
    private $mapper;

    /**
     * Creates a new Coinbase client.
     *
     * @return Client A new Coinbase client
     */
    public static function create(Configuration $configuration)
    {
        return new static(
            $configuration->createHttpClient(),
            $configuration->createMapper()
        );
    }

    public function __construct(HttpClient $http, Mapper $mapper)
    {
        $this->http = $http;
        $this->mapper = $mapper;
    }

    public function getHttpClient()
    {
        return $this->http;
    }

    public function getMapper()
    {
        return $this->mapper;
    }

    /** @return array|null */
    public function decodeLastResponse()
    {
        if ($response = $this->http->getLastResponse()) {
            return $this->mapper->decode($response);
        }
    }

    /**
     * Enables active record methods on resource objects.
     */
    public function enableActiveRecord()
    {
        ActiveRecordContext::setClient($this);
    }

    // data api

    public function getCurrencies(array $params = [])
    {
        return $this->getAndMapData('/v2/currencies', $params);
    }

    public function getExchangeRates($currency = null, array $params = [])
    {
        if ($currency) {
            $params['currency'] = $currency;
        }

        return $this->getAndMapData('/v2/exchange-rates', $params);
    }

    public function getBuyPrice($currency = null, array $params = [])
    {
        if ($currency) {
            $params['currency'] = $currency;
        }

        return $this->getAndMapMoney('/v2/prices/buy', $params);
    }

    public function getSellPrice($currency = null, array $params = [])
    {
        if ($currency) {
            $params['currency'] = $currency;
        }

        return $this->getAndMapMoney('/v2/prices/sell', $params);
    }

    public function getSpotPrice($currency = null, array $params = [])
    {
        if ($currency) {
            $params['currency'] = $currency;
        }

        return $this->getAndMapMoney('/v2/prices/spot', $params);
    }

    public function getHistoricPrices($currency = null, array $params = [])
    {
        if ($currency) {
            $params['currency'] = $currency;
        }

        return $this->getAndMapData('/v2/prices/historic', $params);
    }

    public function getTime(array $params = [])
    {
        return $this->getAndMapData('/v2/time', $params);
    }

    // authentication

    public function refreshAuthentication(array $params = [])
    {
        $this->http->refreshAuthentication($params);
    }

    public function revokeAuthentication(array $params = [])
    {
        $this->http->revokeAuthentication($params);
    }

    // users

    /** @return User */
    public function getUser($userId, array $params = [])
    {
        return $this->getAndMap('/v2/users/'.$userId, $params, 'toUser');
    }

    public function refreshUser(User $user, array $params = [])
    {
        $this->getAndMap($user->getResourcePath(), $params, 'toUser', $user);
    }

    /** @return CurrentUser */
    public function getCurrentUser(array $params = [])
    {
        return $this->getAndMap('/v2/user', $params, 'toUser', new CurrentUser());
    }

    public function getCurrentAuthorization(array $params = [])
    {
        return $this->getAndMapData('/v2/user/auth', $params);
    }

    public function updateCurrentUser(CurrentUser $user, array $params = [])
    {
        $data = $this->mapper->fromCurrentUser($user);
        $response = $this->http->put('/v2/user', $data + $params);
        $this->mapper->toUser($response, $user);
    }

    // accounts

    /**
     * Lists the current user's accounts.
     *
     * Supports pagination parameters.
     *
     * @return ResourceCollection|Account[]
     */
    public function getAccounts(array $params = [])
    {
        return $this->getAndMapCollection('/v2/accounts', $params, 'toAccounts');
    }

    public function loadNextAccounts(ResourceCollection $accounts, array $params = [])
    {
        $this->loadNext($accounts, $params, 'toAccounts');
    }

    /** @return Account */
    public function getAccount($accountId, array $params = [])
    {
        return $this->getAndMap('/v2/accounts/'.$accountId, $params, 'toAccount');
    }

    public function refreshAccount(Account $account, array $params = [])
    {
        $this->getAndMap($account->getResourcePath(), $params, 'toAccount', $account);
    }

    public function createAccount(Account $account, array $params = [])
    {
        $data = $this->mapper->fromAccount($account);
        $this->postAndMap('/v2/accounts', $data + $params, 'toAccount', $account);
    }

    /** @return Account */
    public function getPrimaryAccount(array $params = [])
    {
        return $this->getAndMap('/v2/accounts/primary', $params, 'toAccount');
    }

    public function setPrimaryAccount(Account $account, array $params = [])
    {
        $this->postAndMap($account->getResourcePath().'/primary', $params, 'toAccount', $account);
    }

    public function updateAccount(Account $account, array $params = [])
    {
        $data = $this->mapper->fromAccount($account);
        $response = $this->http->put($account->getResourcePath(), $data + $params);
        $this->mapper->toAccount($response, $account);
    }

    public function deleteAccount(Account $account, array $params = [])
    {
        $this->http->delete($account->getResourcePath(), $params);
    }

    // addresses

    /**
     * Lists addresses for an account.
     *
     * Supports pagination parameters.
     *
     * @return ResourceCollection|Address[]
     */
    public function getAccountAddresses(Account $account, array $params = [])
    {
        return $this->getAndMapCollection($account->getResourcePath().'/addresses', $params, 'toAddresses');
    }

    public function loadNextAddresses(ResourceCollection $addresses, array $params = [])
    {
        $this->loadNext($addresses, $params, 'toAddresses');
    }

    /** @return Address */
    public function getAccountAddress(Account $account, $addressId, array $params = [])
    {
        $path = sprintf('%s/addresses/%s', $account->getResourcePath(), $addressId);

        return $this->getAndMap($path, $params, 'toAddress');
    }

    public function refreshAddress(Address $address, array $params = [])
    {
        $this->getAndMap($address->getResourcePath(), $params, 'toAddress', $address);
    }

    /**
     * Lists transactions for an address.
     *
     * Supports pagination parameters.
     *
     * @return ResourceCollection|Transaction[]
     */
    public function getAddressTransactions(Address $address, array $params = [])
    {
        return $this->getAndMapCollection($address->getResourcePath().'/transactions', $params, 'toTransactions');
    }

    public function loadNextAddressTransactions(ResourceCollection $transactions, array $params = [])
    {
        $this->loadNextTransactions($transactions, $params);
    }

    public function createAccountAddress(Account $account, Address $address, array $params = [])
    {
        $data = $this->mapper->fromAddress($address);
        $this->postAndMap($account->getResourcePath().'/addresses', $data + $params, 'toAddress', $address);
    }

    // transactions

    /**
     * Lists transactions for an account.
     *
     * Supports pagination parameters.
     *
     * @return ResourceCollection|Transaction[]
     */
    public function getAccountTransactions(Account $account, array $params = [])
    {
        return $this->getAndMapCollection($account->getResourcePath().'/transactions', $params, 'toTransactions');
    }

    public function loadNextTransactions(ResourceCollection $transactions, array $params = [])
    {
        $this->loadNext($transactions, $params, 'toTransactions');
    }

    /** @return Transaction */
    public function getAccountTransaction(Account $account, $transactionId, array $params = [])
    {
        $path = sprintf('%s/transactions/%s', $account->getResourcePath(), $transactionId);

        return $this->getAndMap($path, $params, 'toTransaction');
    }

    public function refreshTransaction(Transaction $transaction, array $params = [])
    {
        $this->getAndMap($transaction->getResourcePath(), $params, 'toTransaction', $transaction);
    }

    /**
     * Creates a new transaction.
     *
     * Supported parameters include:
     *
     *  * skip_notifications (Boolean)
     *  * fee (float)
     *  * idem (string)
     */
    public function createAccountTransaction(Account $account, Transaction $transaction, array $params = [])
    {
        $data = $this->mapper->fromTransaction($transaction);
        $this->postAndMap($account->getResourcePath().'/transactions', $data + $params, 'toTransaction', $transaction);
    }

    public function completeTransaction(Transaction $transaction, array $params = [])
    {
        $this->http->post($transaction->getResourcePath().'/complete', $params);
    }

    public function resendTransaction(Transaction $transaction, array $params = [])
    {
        $this->http->post($transaction->getResourcePath().'/resend', $params);
    }

    public function cancelTransaction(Transaction $transaction, array $params = [])
    {
        $this->http->delete($transaction->getResourcePath(), $params);
    }

    // buys

    /**
     * Lists buys for an account.
     *
     * Supports pagination parameters.
     *
     * @return ResourceCollection|Buy[]
     */
    public function getAccountBuys(Account $account, array $params = [])
    {
        return $this->getAndMapCollection($account->getResourcePath().'/buys', $params, 'toBuys');
    }

    public function loadNextBuys(ResourceCollection $buys, array $params = [])
    {
        $this->loadNext($buys, $params, 'toBuys');
    }

    /** @return Buy */
    public function getAccountBuy(Account $account, $buyId, array $params = [])
    {
        $path = sprintf('%s/buys/%s', $account->getResourcePath(), $buyId);

        return $this->getAndMap($path, $params, 'toBuy');
    }

    public function refreshBuy(Buy $buy, array $params = [])
    {
        $this->getAndMap($buy->getResourcePath(), $params, 'toBuy', $buy);
    }

    /**
     * Buys some amount of bitcoin.
     *
     * Supported parameters include:
     *
     *  * agree_btc_amount_varies (Boolean)
     *  * commit (Boolean)
     *  * quote (Boolean)
     */
    public function createAccountBuy(Account $account, Buy $buy, array $params = [])
    {
        $data = $this->mapper->fromBuy($buy);
        $this->postAndMap($account->getResourcePath().'/buys', $data + $params, 'toBuy', $buy);
    }

    public function commitBuy(Buy $buy, array $params = [])
    {
        $this->postAndMap($buy->getResourcePath().'/commit', $params, 'toBuy', $buy);
    }

    // sells

    /**
     * Lists sells for an account.
     *
     * Supports pagination parameters.
     *
     * @return ResourceCollection|Sell[]
     */
    public function getSells(Account $account, array $params = [])
    {
        return $this->getAndMapCollection($account->getResourcePath().'/sells', $params, 'toSells');
    }

    public function loadNextSells(ResourceCollection $sells, array $params = [])
    {
        $this->loadNext($sells, $params, 'toSells');
    }

    /** @return Sell */
    public function getAccountSell(Account $account, $sellId, array $params = [])
    {
        $path = sprintf('%s/sells/%s', $account->getResourcePath(), $sellId);

        return $this->getAndMap($path, $params, 'toSell');
    }

    public function refreshSell(Sell $sell, array $params = [])
    {
        $this->getAndMap($sell->getResourcePath(), $params, 'toSell', $sell);
    }

    /**
     * Sells some amount of bitcoin.
     *
     * Supported parameters include:
     *
     *  * agree_btc_amount_varies (Boolean)
     *  * commit (Boolean)
     *  * quote (Boolean)
     */
    public function createAccountSell(Account $account, Sell $sell, array $params = [])
    {
        $data = $this->mapper->fromSell($sell);
        $this->postAndMap($account->getResourcePath().'/sells', $data + $params, 'toSell', $sell);
    }

    public function commitSell(Sell $sell, array $params = [])
    {
        $this->postAndMap($sell->getResourcePath().'/commit', $params, 'toSell', $sell);
    }

    // deposits

    /**
     * Lists deposits for an account.
     *
     * Supports pagination parameters.
     *
     * @return ResourceCollection|Deposit[]
     */
    public function getAccountDeposits(Account $account, array $params = [])
    {
        return $this->getAndMapCollection($account->getResourcePath().'/deposits', $params, 'toDeposits');
    }

    public function loadNextDeposits(ResourceCollection $deposits, array $params = [])
    {
        $this->loadNext($deposits, $params, 'toDeposits');
    }

    /** @return Deposit */
    public function getAccountDeposit(Account $account, $depositId, array $params = [])
    {
        $path = sprintf('%s/deposits/%s', $account->getResourcePath(), $depositId);

        return $this->getAndMap($path, $params, 'toDeposit');
    }

    public function refreshDeposit(Deposit $deposit, array $params = [])
    {
        $this->getAndMap($deposit->getResourcePath(), $params, 'toDeposit', $deposit);
    }

    /**
     * Deposits some amount of funds.
     *
     * Supported parameters include:
     *
     *  * commit (Boolean)
     */
    public function createAccountDeposit(Account $account, Deposit $deposit, array $params = [])
    {
        $data = $this->mapper->fromDeposit($deposit);
        $this->postAndMap($account->getResourcePath().'/deposits', $data + $params, 'toDeposit', $deposit);
    }

    public function commitDeposit(Deposit $deposit, array $params = [])
    {
        $this->postAndMap($deposit->getResourcePath().'/commit', $params, 'toDeposit', $deposit);
    }

    // withdrawals

    /**
     * Lists withdrawals for an account.
     *
     * Supports pagination parameters.
     *
     * @return ResourceCollection|Withdrawal[]
     */
    public function getAccountWithdrawals(Account $account, array $params = [])
    {
        return $this->getAndMapCollection($account->getResourcePath().'/withdrawals', $params, 'toWithdrawals');
    }

    public function loadNextWithdrawals(ResourceCollection $withdrawals, array $params = [])
    {
        $this->loadNext($withdrawals, $params, 'toWithdrawals');
    }

    /** @return Withdrawal */
    public function getAccountWithdrawal(Account $account, $withdrawalId, array $params = [])
    {
        $path = sprintf('%s/withdrawals/%s', $account->getResourcePath(), $withdrawalId);

        return $this->getAndMap($path, $params, 'toWithdrawal');
    }

    public function refreshWithdrawal(Withdrawal $withdrawal, array $params = [])
    {
        $this->getAndMap($withdrawal->getResourcePath(), $params, 'toWithdrawal', $withdrawal);
    }

    /**
     * Withdraws some amount of funds.
     *
     * Supported parameters include:
     *
     *  * commit (Boolean)
     */
    public function createAccountWithdrawal(Account $account, Withdrawal $withdrawal, array $params = [])
    {
        $data = $this->mapper->fromWithdrawal($withdrawal);
        $this->postAndMap($account->getResourcePath().'/withdrawals', $data + $params, 'toWithdrawal', $withdrawal);
    }

    public function commitWithdrawal(Withdrawal $withdrawal, array $params = [])
    {
        $this->postAndMap($withdrawal->getResourcePath().'/commit', $params, 'toWithdrawal', $withdrawal);
    }

    // payment methods

    /**
     * Lists payment methods for the current user.
     *
     * Supports pagination parameters.
     *
     * @return ResourceCollection|PaymentMethod[]
     */
    public function getPaymentMethods(array $params = [])
    {
        return $this->getAndMapCollection('/v2/payment-methods', $params, 'toPaymentMethods');
    }

    public function loadNextPaymentMethods(ResourceCollection $paymentMethods, array $params = [])
    {
        $this->loadNext($paymentMethods, $params, 'toPaymentMethods');
    }

    /** @return PaymentMethod */
    public function getPaymentMethod($paymentMethodId, array $params = [])
    {
        return $this->getAndMap('/v2/payment-methods/'.$paymentMethodId, $params, 'toPaymentMethod');
    }

    public function refreshPaymentMethod(PaymentMethod $paymentMethod, array $params = [])
    {
        $this->getAndMap($paymentMethod->getResourcePath(), $params, 'toPaymentMethod', $paymentMethod);
    }

    // merchant api

    /** @return Merchant */
    public function getMerchant($merchantId, array $params = [])
    {
        return $this->getAndMap('/v2/merchants/'.$merchantId, $params, 'toMerchant');
    }

    public function refreshMerchant(Merchant $merchant, array $params = [])
    {
        $this->getAndMap($merchant->getResourcePath(), $params, 'toMerchant', $merchant);
    }

    /**
     * Lists orders for the current user.
     *
     * Supports pagination parameters.
     *
     * @return ResourceCollection|Order[]
     */
    public function getOrders(array $params = [])
    {
        return $this->getAndMapCollection('/v2/orders', $params, 'toOrders');
    }

    public function loadNextOrders(ResourceCollection $orders, array $params = [])
    {
        $this->loadNext($orders, $params, 'toOrders');
    }

    /** @return Order */
    public function getOrder($orderId, array $params = [])
    {
        return $this->getAndMap('/v2/orders/'.$orderId, $params, 'toOrder');
    }

    public function refreshOrder(Order $order, array $params = [])
    {
        $this->getAndMap($order->getResourcePath(), $params, 'toOrder', $order);
    }

    public function createOrder(Order $order, array $params = [])
    {
        $data = $this->mapper->fromOrder($order);
        $this->postAndMap('/v2/orders', $data + $params, 'toOrder', $order);
    }

    /**
     * Refunds an order.
     *
     * Supported parameters include:
     *
     *  * mispayment (string)
     *  * refund_address (string)
     */
    public function refundOrder(Order $order, $currency, array $params = [])
    {
        $params['currency'] = $currency;

        $this->postAndMap($order->getResourcePath().'/refund', $params, 'toOrder', $order);
    }

    /**
     * Lists checkouts for the current user.
     *
     * Supports pagination parameters.
     *
     * @return ResourceCollection|Checkout[]
     */
    public function getCheckouts(array $params = [])
    {
        return $this->getAndMapCollection('/v2/checkouts', $params, 'toCheckouts');
    }

    public function loadNextCheckouts(ResourceCollection $checkouts, array $params = [])
    {
        $this->loadNext($checkouts, $params, 'toCheckouts');
    }

    /** @return Checkout */
    public function getCheckout($checkoutId, array $params = [])
    {
        return $this->getAndMap('/v2/checkouts/'.$checkoutId, $params, 'toCheckout');
    }

    public function refreshCheckout(Checkout $checkout, array $params = [])
    {
        $this->getAndMap($checkout->getResourcePath(), $params, 'toCheckout', $checkout);
    }

    public function createCheckout(Checkout $checkout, array $params = [])
    {
        $data = $this->mapper->fromCheckout($checkout);
        $this->postAndMap('/v2/checkouts', $data + $params, 'toCheckout', $checkout);
    }

    /**
     * Lists notifications where the current user was the subscriber.
     *
     * Supports pagination parameters.
     *
     * @return ResourceCollection|Notification[]
     */
    public function getNotifications(array $params = [])
    {
        return $this->getAndMapCollection('/v2/notifications', $params, 'toNotifications');
    }

    public function loadNextNotifications(ResourceCollection $notifications, array $params = [])
    {
        $this->loadNext($notifications, $params, 'toNotifications');
    }

    /** @return Notification */
    public function getNotification($notificationId, array $params = [])
    {
        return $this->getAndMap('/v2/notifications/'.$notificationId, $params, 'toNotification');
    }

    public function refreshNotification(Notification $notification, array $params = [])
    {
        $this->getAndMap($notification->getResourcePath(), $params, 'toNotification', $notification);
    }

    /**
     * Create a Notification object from the body of a notification webhook
     *
     * @return Notification
     */
    public function parseNotification($webhook_body)
    {
        $data = json_decode($webhook_body, true);
        return $this->mapper->injectNotification($data);
    }

    /**
     * Verifies the authenticity of a merchant callback from Coinbase
     *
     * @return Boolean
     */
    public function verifyCallback($body, $signature)
    {
        $signature_buffer = base64_decode( $signature );
        return (1 == openssl_verify($body, $signature_buffer, self::getCallbackPublicKey(), OPENSSL_ALGO_SHA256));
    }

    /**
     * Return the PEM encoded public RSA key for merchant callbacks
     *
     * @return String
     */
    public static function getCallbackPublicKey()
    {
        $key = <<<EOD
-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEA9MsJBuXzFGIh/xkAA9Cy
QdZKRerV+apyOAWY7sEYV/AJg+AX/tW2SHeZj+3OilNYm5DlBi6ZzDboczmENrFn
mUXQsecsR5qjdDWb2qYqBkDkoZP02m9o9UmKObR8coKW4ZBw0hEf3fP9OEofG2s7
Z6PReWFyQffnnecwXJoN22qjjsUtNNKOOo7/l+IyGMVmdzJbMWQS4ybaU9r9Ax0J
4QUJSS/S4j4LP+3Z9i2DzIe4+PGa4Nf7fQWLwE45UUp5SmplxBfvEGwYNEsHvmRj
usIy2ZunSO2CjJ/xGGn9+/57W7/SNVzk/DlDWLaN27hUFLEINlWXeYLBPjw5GGWp
ieXGVcTaFSLBWX3JbOJ2o2L4MxinXjTtpiKjem9197QXSVZ/zF1DI8tRipsgZWT2
/UQMqsJoVRXHveY9q9VrCLe97FKAUiohLsskr0USrMCUYvLU9mMw15hwtzZlKY8T
dMH2Ugqv/CPBuYf1Bc7FAsKJwdC504e8kAUgomi4tKuUo25LPZJMTvMTs/9IsRJv
I7ibYmVR3xNsVEpupdFcTJYGzOQBo8orHKPFn1jj31DIIKociCwu6m8ICDgLuMHj
7bUHIlTzPPT7hRPyBQ1KdyvwxbguqpNhqp1hG2sghgMr0M6KMkUEz38JFElsVrpF
4z+EqsFcIZzjkSG16BjjjTkCAwEAAQ==
-----END PUBLIC KEY-----
EOD;
        return $key;
    }

    /**
     * Lists orders for a checkout.
     *
     * Supports pagination parameters.
     *
     * @return ResourceCollection|Order[]
     */
    public function getCheckoutOrders(Checkout $checkout, array $params = [])
    {
        return $this->getAndMapCollection($checkout->getResourcePath().'/orders', $params, 'toOrders');
    }

    public function loadNextCheckoutOrders(ResourceCollection $orders, array $params = [])
    {
        $this->loadNextOrders($orders, $params);
    }

    /** @return Order */
    public function createNewCheckoutOrder(Checkout $checkout, array $params = [])
    {
        return $this->postAndMap($checkout->getResourcePath().'/orders', $params, 'toOrder');
    }

    // private

    private function getAndMapData($path, array $params = [])
    {
        $response = $this->http->get($path, $params);

        return $this->mapper->toData($response);
    }

    private function getAndMapMoney($path, array $params = [])
    {
        $response = $this->http->get($path, $params);

        return $this->mapper->toMoney($response);
    }

    /** @return ResourceCollection|Resource[] */
    private function getAndMapCollection($path, array $params, $mapperMethod)
    {
        $fetchAll = isset($params[Param::FETCH_ALL]) ? $params[Param::FETCH_ALL] : false;
        unset($params[Param::FETCH_ALL]);

        $response = $this->http->get($path, $params);

        /** @var ResourceCollection $collection */
        $collection = $this->mapper->$mapperMethod($response);

        if ($fetchAll) {
            while ($collection->hasNextPage()) {
                $this->loadNext($collection, $params, $mapperMethod);
            }
        }

        return $collection;
    }

    /** @return Resource */
    private function getAndMap($path, array $params, $mapperMethod, Resource $resource = null)
    {
        $response = $this->http->get($path, $params);

        return $this->mapper->$mapperMethod($response, $resource);
    }

    private function postAndMap($path, array $params, $mapperMethod, Resource $resource = null)
    {
        $response = $this->http->post($path, $params);

        return $this->mapper->$mapperMethod($response, $resource);
    }

    private function loadNext(ResourceCollection $collection, array $params, $mapperMethod)
    {
        $response = $this->http->get($collection->getNextUri(), $params);
        $nextPage = $this->mapper->$mapperMethod($response);
        $collection->mergeNextPage($nextPage);
    }
}
