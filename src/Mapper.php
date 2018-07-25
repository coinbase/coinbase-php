<?php

namespace Coinbase\Wallet;

use Coinbase\Wallet\Enum\ResourceType;
use Coinbase\Wallet\Exception\LogicException;
use Coinbase\Wallet\Exception\RuntimeException;
use Coinbase\Wallet\Resource\Account;
use Coinbase\Wallet\Resource\Address;
use Coinbase\Wallet\Resource\Application;
use Coinbase\Wallet\Resource\BitcoinAddress;
use Coinbase\Wallet\Resource\BitcoinCashAddress;
use Coinbase\Wallet\Resource\Buy;
use Coinbase\Wallet\Resource\Checkout;
use Coinbase\Wallet\Resource\CurrentUser;
use Coinbase\Wallet\Resource\Deposit;
use Coinbase\Wallet\Resource\Email;
use Coinbase\Wallet\Resource\EthereumNetwork;
use Coinbase\Wallet\Resource\EthrereumAddress;
use Coinbase\Wallet\Resource\LitecoinAddress;
use Coinbase\Wallet\Resource\LitecoinNetwork;
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
use Coinbase\Wallet\Resource\BitcoinNetwork;
use Coinbase\Wallet\Resource\BitcoinCashNetwork;
use Coinbase\Wallet\Value\Fee;
use Coinbase\Wallet\Value\Money;
use Coinbase\Wallet\Value\Network;
use Psr\Http\Message\ResponseInterface;


class Mapper
{
    private $reflection = [];

    // users

    /** @return User */
    public function toUser(ResponseInterface $response, User $user = null)
    {
        return $this->injectUser($this->decode($response)['data'], $user);
    }

    /** @return array */
    public function fromCurrentUser(CurrentUser $user)
    {
        return array_intersect_key(
            $this->extractData($user),
            array_flip(['name', 'time_zone', 'native_currency'])
        );
    }

    // accounts

    /** @return ResourceCollection */
    public function toAccounts(ResponseInterface $response)
    {
        return $this->toCollection($response, 'injectAccount');
    }

    /** @return Account */
    public function toAccount(ResponseInterface $response, Account $account = null)
    {
        return $this->injectAccount($this->decode($response)['data'], $account);
    }

    /** @return array */
    public function fromAccount(Account $account)
    {
        return array_intersect_key(
            $this->extractData($account),
            array_flip(['name'])
        );
    }

    // addresses

    /** @return ResourceCollection */
    public function toAddresses(ResponseInterface $response)
    {
        return $this->toCollection($response, 'injectAddress');
    }

    /** @return Address */
    public function toAddress(ResponseInterface $response, Address $address = null)
    {
        return $this->injectAddress($this->decode($response)['data'], $address);
    }

    /** @return array */
    public function fromAddress(Address $address)
    {
        return array_intersect_key(
            $this->extractData($address),
            array_flip(['name', 'callback_url'])
        );
    }

    // transactions

    /** @return ResourceCollection */
    public function toTransactions(ResponseInterface $response)
    {
        return $this->toCollection($response, 'injectTransaction');
    }

    /** @return Transaction */
    public function toTransaction(ResponseInterface $response, Transaction $transaction = null)
    {
        return $this->injectTransaction($this->decode($response)['data'], $transaction);
    }

    /** @return array */
    public function fromTransaction(Transaction $transaction)
    {
        // validate
        $to = $transaction->getTo();
        if ($to && !$to instanceof Email && !$to instanceof BitcoinAddress && !$to instanceof LitecoinAddress && !$to instanceof EthrereumAddress && !$to instanceof BitcoinCashAddress  && !$to instanceof Account) {
            throw new LogicException(
                'The Coinbase API only accepts transactions to an account, email, bitcoin address, bitcoin cash address, litecoin address, or ethereum address'
            );
        }

        // filter
        $data = array_intersect_key(
            $this->extractData($transaction),
            array_flip(['type', 'to', 'amount', 'description', 'fee'])
        );

        // to
        if (isset($data['to']['address'])) {
            $data['to'] = $data['to']['address'];
        } elseif (isset($data['to']['email'])) {
            $data['to'] = $data['to']['email'];
        } elseif (isset($data['to']['id'])) {
            $data['to'] = $data['to']['id'];
        }

        // currency
        if (isset($data['amount']['currency'])) {
            $data['currency'] = $data['amount']['currency'];
        }

        // amount
        if (isset($data['amount']['amount'])) {
            $data['amount'] = $data['amount']['amount'];
        }

        return $data;
    }

    // buys

    /** @return ResourceCollection */
    public function toBuys(ResponseInterface $response)
    {
        return $this->toCollection($response, 'injectBuy');
    }

    /** @return Buy */
    public function toBuy(ResponseInterface $response, Buy $buy = null)
    {
        return $this->injectBuy($this->decode($response)['data'], $buy);
    }

    /** @return array */
    public function fromBuy(Buy $buy)
    {
        // validate
        if ($buy->getAmount() && $buy->getTotal()) {
            throw new LogicException(
                'The Coinbase API accepts buys with either an amount or a total, but not both'
            );
        }

        // filter
        $data = array_intersect_key(
            $this->extractData($buy),
            array_flip(['amount', 'total', 'payment_method'])
        );

        // currency
        if (isset($data['amount']['currency'])) {
            $data['currency'] = $data['amount']['currency'];
        } elseif (isset($data['total']['currency'])) {
            $data['currency'] = $data['total']['currency'];
        }

        // amount
        if (isset($data['amount']['amount'])) {
            $data['amount'] = $data['amount']['amount'];
        }

        // total
        if (isset($data['total']['amount'])) {
            $data['total'] = $data['total']['amount'];
        }

        // payment method
        if (isset($data['payment_method']['id'])) {
            $data['payment_method'] = $data['payment_method']['id'];
        }

        return $data;
    }

    // sells

    /** @return ResourceCollection */
    public function toSells(ResponseInterface $response)
    {
        return $this->toCollection($response, 'injectSell');
    }

    /** @return Sell */
    public function toSell(ResponseInterface $response, Sell $sell = null)
    {
        return $this->injectSell($this->decode($response)['data'], $sell);
    }

    /** @return array */
    public function fromSell(Sell $sell)
    {
        // validate
        if ($sell->getAmount() && $sell->getTotal()) {
            throw new LogicException(
                'The Coinbase API accepts sells with either an amount or a total, but not both'
            );
        }

        // filter
        $data = array_intersect_key(
            $this->extractData($sell),
            array_flip(['amount', 'total', 'payment_method'])
        );

        // currency
        if (isset($data['amount']['currency'])) {
            $data['currency'] = $data['amount']['currency'];
        } elseif (isset($data['total']['currency'])) {
            $data['currency'] = $data['total']['currency'];
        }

        // amount
        if (isset($data['amount']['amount'])) {
            $data['amount'] = $data['amount']['amount'];
        }

        // total
        if (isset($data['total']['amount'])) {
            $data['total'] = $data['total']['amount'];
        }

        // payment method
        if (isset($data['payment_method']['id'])) {
            $data['payment_method'] = $data['payment_method']['id'];
        }

        return $data;
    }

    // deposits

    /** @return ResourceCollection */
    public function toDeposits(ResponseInterface $response)
    {
        return $this->toCollection($response, 'injectDeposit');
    }

    /** @return Deposit */
    public function toDeposit(ResponseInterface $response, Deposit $deposit = null)
    {
        return $this->injectDeposit($this->decode($response)['data'], $deposit);
    }

    /** @return array */
    public function fromDeposit(Deposit $deposit)
    {
        // filter
        $data = array_intersect_key(
            $this->extractData($deposit),
            array_flip(['amount', 'payment_method'])
        );

        // currency
        if (isset($data['amount']['currency'])) {
            $data['currency'] = $data['amount']['currency'];
        }

        // amount
        if (isset($data['amount']['amount'])) {
            $data['amount'] = $data['amount']['amount'];
        }

        // payment method
        if (isset($data['payment_method']['id'])) {
            $data['payment_method'] = $data['payment_method']['id'];
        }

        return $data;
    }

    // withdrawals

    /** @return ResourceCollection */
    public function toWithdrawals(ResponseInterface $response)
    {
        return $this->toCollection($response, 'injectWithdrawal');
    }

    /** @return Withdrawal */
    public function toWithdrawal(ResponseInterface $response, Withdrawal $withdrawal = null)
    {
        return $this->injectWithdrawal($this->decode($response)['data'], $withdrawal);
    }

    /** @return array */
    public function fromWithdrawal(Withdrawal $withdrawal)
    {
        // filter
        $data = array_intersect_key(
            $this->extractData($withdrawal),
            array_flip(['amount', 'payment_method'])
        );

        // currency
        if (isset($data['amount']['currency'])) {
            $data['currency'] = $data['amount']['currency'];
        }

        // amount
        if (isset($data['amount']['amount'])) {
            $data['amount'] = $data['amount']['amount'];
        }

        // payment method
        if (isset($data['payment_method']['id'])) {
            $data['payment_method'] = $data['payment_method']['id'];
        }

        return $data;
    }

    // payment methods

    /** @return ResourceCollection */
    public function toPaymentMethods(ResponseInterface $response)
    {
        return $this->toCollection($response, 'injectPaymentMethod');
    }

    /** @return PaymentMethod */
    public function toPaymentMethod(ResponseInterface $response, PaymentMethod $paymentMethod = null)
    {
        return $this->injectPaymentMethod($this->decode($response)['data'], $paymentMethod);
    }

    // merchants

    /** @return Merchant */
    public function toMerchant(ResponseInterface $response, Merchant $merchant = null)
    {
        return $this->injectMerchant($this->decode($response)['data'], $merchant);
    }

    // orders

    /** @return ResourceCollection */
    public function toOrders(ResponseInterface $response)
    {
        return $this->toCollection($response, 'injectOrder');
    }

    /** @return Order */
    public function toOrder(ResponseInterface $response, Order $order = null)
    {
        return $this->injectOrder($this->decode($response)['data'], $order);
    }

    /** @return array */
    public function fromOrder(Order $order)
    {
        // filter
        $data = array_intersect_key(
            $this->extractData($order),
            array_flip(['amount', 'name', 'description', 'notifications_url', 'metadata'])
        );

        // currency
        if (isset($data['amount']['currency'])) {
            $data['currency'] = $data['amount']['currency'];
        }

        // amount
        if (isset($data['amount']['amount'])) {
            $data['amount'] = $data['amount']['amount'];
        }

        return $data;
    }

    // checkouts

    /** @return ResourceCollection */
    public function toCheckouts(ResponseInterface $response)
    {
        return $this->toCollection($response, 'injectCheckout');
    }

    /** @return Checkout */
    public function toCheckout(ResponseInterface $response, Checkout $checkout = null)
    {
        return $this->injectCheckout($this->decode($response)['data'], $checkout);
    }

    /** @return array */
    public function fromCheckout(Checkout $checkout)
    {
        $keys = [
            'amount', 'name', 'description', 'type', 'style',
            'customer_defined_amount', 'amount_presets', 'notifications_url', 'success_url',
            'cancel_url', 'auto_redirect', 'collect_shipping_address',
            'collect_email', 'collect_phone_number', 'collect_country',
            'metadata',
        ];

        // filter
        $data = array_intersect_key(
            $this->extractData($checkout),
            array_flip($keys)
        );

        // currency
        if (isset($data['amount']['currency'])) {
            $data['currency'] = $data['amount']['currency'];
        }

        // amount
        if (isset($data['amount']['amount'])) {
            $data['amount'] = $data['amount']['amount'];
        }

        return $data;
    }

    // notifications

    /** @return ResourceCollection */
    public function toNotifications(ResponseInterface $response)
    {
        return $this->toCollection($response, 'injectNotification');
    }

    /** @return Notification */
    public function toNotification(ResponseInterface $response, Notification $notification = null)
    {
        return $this->injectNotification($this->decode($response)['data'], $notification);
    }

    // misc

    /** @return array */
    public function toData(ResponseInterface $response)
    {
        return $this->decode($response)['data'];
    }

    /** @return Money|null */
    public function toMoney(ResponseInterface $response)
    {
        $data = $this->decode($response)['data'];

        return new Money($data['amount'], $data['currency']);
    }

    /** @return array */
    public function decode(ResponseInterface $response)
    {
        return json_decode($response->getBody(), true);
    }

    // private

    private function toCollection(ResponseInterface $response, $method)
    {
        $data = $this->decode($response);

        if (isset($data['pagination'])) {
            $coll = new ResourceCollection(
                $data['pagination']['previous_uri'],
                $data['pagination']['next_uri']
            );
        } else {
            $coll = new ResourceCollection();
        }

        foreach ($data['data'] as $resource) {
            $coll->add($this->$method($resource));
        }

        return $coll;
    }

    private function injectUser(array $data, User $user = null)
    {
        return $this->injectResource($data, $user ?: new User());
    }

    private function injectAccount(array $data, Account $account = null)
    {
        return $this->injectResource($data, $account ?: new Account());
    }

    private function injectAddress(array $data, Address $address = null)
    {
        return $this->injectResource($data, $address ?: new Address());
    }

    private function injectApplication(array $data, Application $application = null)
    {
        return $this->injectResource($data, $application ?: new Application());
    }

    private function injectTransaction(array $data, Transaction $transaction = null)
    {
        return $this->injectResource($data, $transaction ?: new Transaction());
    }

    private function injectBuy(array $data, Buy $buy = null)
    {
        return $this->injectResource($data, $buy ?: new Buy());
    }

    private function injectSell(array $data, Sell $sell = null)
    {
        return $this->injectResource($data, $sell ?: new Sell());
    }

    private function injectDeposit(array $data, Deposit $deposit = null)
    {
        return $this->injectResource($data, $deposit ?: new Deposit());
    }

    private function injectWithdrawal(array $data, Withdrawal $withdrawal = null)
    {
        return $this->injectResource($data, $withdrawal ?: new Withdrawal());
    }

    private function injectPaymentMethod(array $data, PaymentMethod $paymentMethod = null)
    {
        return $this->injectResource($data, $paymentMethod ?: new PaymentMethod());
    }

    private function injectMerchant(array $data, Merchant $merchant = null)
    {
        return $this->injectResource($data, $merchant ?: new Merchant());
    }

    private function injectOrder(array $data, Order $order = null)
    {
        return $this->injectResource($data, $order ?: new Order());
    }

    private function injectCheckout(array $data, Checkout $checkout = null)
    {
        return $this->injectResource($data, $checkout ?: new Checkout());
    }

    public function injectNotification(array $data, Notification $notification = null)
    {
        return $this->injectResource($data, $notification ?: new Notification());
    }

    private function injectResource(array $data, Resource $resource)
    {
        $properties = $this->getReflectionProperties($resource);

        // add raw data to object
        $properties['raw_data']->setValue($resource, $data);

        foreach ($properties as $key => $property) {
            if (isset($data[$key])) {
                $property->setValue($resource, $this->toPhp($key, $data[$key]));
            }
        }

        return $resource;
    }

    private function extractData(Resource $resource)
    {
        $data = [];
        foreach ($this->getReflectionProperties($resource) as $key => $property) {
            if (null !== $value = $this->fromPhp($property->getValue($resource))) {
                $data[$key] = $value;
            }
        }

        // remove raw data from array
        unset($data['raw_data']);

        return $data;
    }

    /** @return \ReflectionProperty[] */
    private function getReflectionProperties(Resource $resource)
    {
        $type = $resource->getResourceType();

        if (isset($this->reflection[$type])) {
            return $this->reflection[$type];
        }

        $class = new \ReflectionObject($resource);
        $properties = [];
        do {
            foreach ($class->getProperties() as $property) {
                $property->setAccessible(true);
                $properties[self::snakeCase($property->getName())] = $property;
            }
        } while ($class = $class->getParentClass());

        return $this->reflection[$type] = $properties;
    }

    private function toPhp($key, $value)
    {
        if ('_at' === substr($key, -3)) {
            // timestamp
            return new \DateTime($value);
        }

        if (is_scalar($value)) {
            // misc
            return $value;
        }

        if (is_integer(key($value))) {
            // list
            $list = [];
            foreach ($value as $k => $v) {
                $list[$k] = $this->toPhp($k, $v);
            }

            return $list;
        }

        if (isset($value['resource'])) {
            // resource
            return $this->createResource($value['resource'], $value);
        }

        if (isset($value['amount']) && isset($value['currency'])) {
            // money
            return new Money($value['amount'], $value['currency']);
        }

        if ('network' === $key && isset($value['status'])) {
            // network
            return new Network($value['status'], isset($value['hash']) ? $value['hash'] : null, isset($value['transaction_fee']) ? $value['transaction_fee'] : null);
        }

        if (isset($value['type']) && isset($value['amount']) && isset($value['amount']['amount']) && isset($value['amount']['currency'])) {
            // fee
            return new Fee($value['type'], new Money($value['amount']['amount'], $value['amount']['currency']));
        }

        return $value;
    }

    private function fromPhp($value)
    {
        if (is_scalar($value)) {
            // misc
            return $value;
        }

        if (is_array($value)) {
            // list
            $list = [];
            foreach ($value as $k => $v) {
                $list[$k] = $this->fromPhp($v);
            }

            return $list;
        }

        if ($value instanceof \DateTime) {
            // timestamp
            return $value->format(\DateTime::ISO8601);
        }

        if ($value instanceof Email) {
            // email
            return [
                'resource' => ResourceType::EMAIL,
                'email' => $value->getEmail(),
            ];
        }

        if ($value instanceof BitcoinAddress) {
            // bitcoin address
            return [
                'resource' => ResourceType::BITCOIN_ADDRESS,
                'address' => $value->getAddress(),
            ];
        }

        if($value instanceof BitcoinCashAddress){
            // bitcoin-cash address
            return [
                'resource' => ResourceType::BITCOIN_CASH_ADDRESS,
                'address' => $value->getAddress(),
            ];
        }

        if($value instanceof LitecoinAddress){
            // litecoin address
            return [
                'resource' => ResourceType::LITECOIN_ADDRESS,
                'address' => $value->getAddress(),
            ];
        }

        if($value instanceof EthrereumAddress){
            // ethereum address
            return [
                'resource' => ResourceType::ETHEREUM_ADDRESS,
                'address' => $value->getAddress(),
            ];
        }

        if ($value instanceof Resource) {
            // resource
            return [
                'id' => $value->getId(),
                'resource' => $value->getResourceType(),
                'resource_path' => $value->getResourcePath(),
            ];
        }

        if ($value instanceof Money) {
            // money
            return [
                'amount' => $value->getAmount(),
                'currency' => $value->getCurrency(),
            ];
        }

        if ($value instanceof Network) {
            // network
            $data = ['status' => $value->getStatus()];
            if ($hash = $value->getHash()) {
                $data['hash'] = $hash;
            }

            return $data;
        }

        if ($value instanceof Fee) {
            // fee
            return [
                'type' => $value->getType(),
                'amount' => [
                    'amount' => $value->getAmount()->getAmount(),
                    'currency' => $value->getAmount()->getCurrency(),
                ],
            ];
        }

        // fail quietly
        return $value;
    }

    private static function snakeCase($word)
    {
        // copied from doctrine/inflector
        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $word));
    }

    private function createResource($type, array $data)
    {
        $expanded = $this->isExpanded($data);

        switch ($type) {
            case ResourceType::ACCOUNT:
                return $expanded ? $this->injectAccount($data) : new Account($data['resource_path']);
            case ResourceType::ADDRESS:
                return $expanded ? $this->injectAddress($data) : new Address($data['resource_path']);
            case ResourceType::APPLICATION:
                return $expanded ? $this->injectApplication($data) : new Application($data['resource_path']);
            case ResourceType::BITCOIN_ADDRESS:
                return new BitcoinAddress($data['address']);
            case ResourceType::BUY:
                return $expanded ? $this->injectBuy($data) : new Buy($data['resource_path']);
            case ResourceType::CHECKOUT:
                return $expanded ? $this->injectCheckout($data) : new Checkout($data['resource_path']);
            case ResourceType::DEPOSIT:
                return $expanded ? $this->injectDeposit($data) : new Deposit($data['resource_path']);
            case ResourceType::EMAIL:
                return new Email($data['email']);
            case ResourceType::MERCHANT:
                return $expanded ? $this->injectMerchant($data) : new Merchant($data['resource_path']);
            case ResourceType::ORDER:
                return $expanded ? $this->injectOrder($data) : new Order($data['resource_path']);
            case ResourceType::PAYMENT_METHOD:
                return $expanded ? $this->injectPaymentMethod($data) : new PaymentMethod($data['resource_path']);
            case ResourceType::SELL:
                return $expanded ? $this->injectSell($data) : new Sell($data['resource_path']);
            case ResourceType::TRANSACTION:
                return $expanded ? $this->injectTransaction($data) : new Transaction(null, $data['resource_path']);
            case ResourceType::USER:
                return $expanded ? $this->injectUser($data) : new User($data['resource_path']);
            case ResourceType::WITHDRAWAL:
                return $expanded ? $this->injectWithdrawal($data) : new Withdrawal($data['resource_path']);
            case ResourceType::NOTIFICATION:
                return $expanded ? $this->injectNotification($data) : new Notification($data['resource_path']);
            case ResourceType::BITCOIN_NETWORK:
                return new BitcoinNetwork();
            case ResourceType::BITCOIN_CASH_NETWORK:
                return new BitcoinCashNetwork();
            case ResourceType::LITECOIN_NETWORK:
                return new LitecoinNetwork();
            case ResourceType::ETHEREUM_NETWORK:
                return new EthereumNetwork();
            case ResourceType::LITECOIN_ADDRESS:
                return $expanded ? $this->injectAddress($data) : new Address($data['resource_path']);
            case ResourceType::ETHEREUM_ADDRESS:
                return $expanded ? $this->injectAddress($data) : new Address($data['resource_path']);
            case ResourceType::BITCOIN_CASH_ADDRESS:
                return $expanded ? $this->injectAddress($data) : new Address($data['resource_path']);
            default:
                throw new RuntimeException('Unrecognized resource type: '.$type);
        }
    }

    /**
     * Checks if a data array represents an expanded resource.
     *
     * @return Boolean Whether the data array represents a complete resource
     */
    private function isExpanded(array $data)
    {
        return (Boolean) array_diff(array_keys($data), ['id', 'resource', 'resource_path']);
    }
}
