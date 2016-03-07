<?php

namespace Coinbase\Wallet\Tests;

use Coinbase\Wallet\Enum\CurrencyCode;
use Coinbase\Wallet\Enum\TransactionType;
use Coinbase\Wallet\Mapper;
use Coinbase\Wallet\Resource\Account;
use Coinbase\Wallet\Resource\Buy;
use Coinbase\Wallet\Resource\Deposit;
use Coinbase\Wallet\Resource\Email;
use Coinbase\Wallet\Resource\PaymentMethod;
use Coinbase\Wallet\Resource\Sell;
use Coinbase\Wallet\Resource\Transaction;
use Coinbase\Wallet\Resource\User;
use Coinbase\Wallet\Resource\Withdrawal;
use Coinbase\Wallet\Value\Fee;
use Coinbase\Wallet\Value\Money;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class MapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var Mapper */
    private $mapper;

    public static function setUpBeforeClass()
    {
        date_default_timezone_set('America/New_York');
    }

    protected function setUp()
    {
        $this->mapper = new Mapper();
    }

    protected function tearDown()
    {
        $this->mapper = null;
    }

    public function testToNotification()
    {
        $response = $this->getMockResponse(['data' => self::$notification]);
        $notification = $this->mapper->toNotification($response);

        $this->assertEquals(self::$notification['id'], $notification->getId());
        $this->assertEquals(self::$notification['type'], $notification->getType());
        $this->assertEquals(1, $notification->getDeliveryAttempts());

        $this->assertInstanceOf(Buy::class, $notification->getData());
        $this->assertEquals(self::$notification['data']['id'], $notification->getData()->getId());

        $this->assertInstanceOf(User::class, $notification->getUser());
        $this->assertEquals(self::$notification['user']['id'], $notification->getUser()->getId());

        $this->assertInstanceOf(Account::class, $notification->getAccount());
        $this->assertEquals(self::$notification['account']['id'], $notification->getAccount()->getId());

        $this->assertEquals(self::$notification, $notification->getRawData());
    }

    public function testToBuy()
    {
        $response = $this->getMockResponse(['data' => self::$buy]);
        $buy = $this->mapper->toBuy($response);

        $this->assertEquals(self::$buy['id'], $buy->getId());
        $this->assertEquals(self::$buy['status'], $buy->getStatus());
        $this->assertInstanceOf(PaymentMethod::class, $buy->getPaymentMethod());
        $this->assertEquals(self::$buy['payment_method']['id'], $buy->getPaymentMethod()->getId());
        $this->assertInstanceOf(Transaction::class, $buy->getTransaction());
        $this->assertEquals(self::$buy['transaction']['id'], $buy->getTransaction()->getId());
        $this->assertInstanceOf(Money::class, $buy->getAmount());
        $this->assertEquals(self::$buy['amount']['amount'], $buy->getAmount()->getAmount());
        $this->assertEquals(self::$buy['amount']['currency'], $buy->getAmount()->getCurrency());
        $this->assertInstanceOf(Money::class, $buy->getTotal());
        $this->assertEquals(self::$buy['total']['amount'], $buy->getTotal()->getAmount());
        $this->assertEquals(self::$buy['total']['currency'], $buy->getTotal()->getCurrency());
        $this->assertInstanceOf(Money::class, $buy->getSubtotal());
        $this->assertEquals(self::$buy['subtotal']['amount'], $buy->getSubtotal()->getAmount());
        $this->assertEquals(self::$buy['subtotal']['currency'], $buy->getSubtotal()->getCurrency());
        $this->assertInstanceOf(\DateTime::class, $buy->getCreatedAt());
        $this->assertInstanceOf(\DateTime::class, $buy->getUpdatedAt());
        $this->assertEquals(self::$buy['resource_path'], $buy->getResourcePath());
        $this->assertEquals(self::$buy['committed'], $buy->isCommitted());
        $this->assertEquals(self::$buy['instant'], $buy->isInstant());
        $this->assertInternalType('array', $buy->getFees());
        $this->assertArrayHasKey(0, $buy->getFees());
        $this->assertInstanceOf(Fee::class, $buy->getFees()[0]);
        $this->assertEquals(self::$buy['fees'][0]['type'], $buy->getFees()[0]->getType());
        $this->assertInstanceOf(Money::class, $buy->getFees()[0]->getAmount());
        $this->assertEquals(self::$buy['fees'][0]['amount']['amount'], $buy->getFees()[0]->getAmount()->getAmount());
        $this->assertEquals(self::$buy['fees'][0]['amount']['currency'], $buy->getFees()[0]->getAmount()->getCurrency());
        $this->assertInstanceOf(\DateTime::class, $buy->getPayoutAt());
        $this->assertEquals(self::$buy, $buy->getRawData());
    }

    public function testFromBuy()
    {
        $buy = new Buy();
        $buy->setAmount(new Money(1, CurrencyCode::BTC));
        $buy->setPaymentMethod(new PaymentMethod('PAYMENT_METHOD_ID'));

        $data = $this->mapper->fromBuy($buy);

        $this->assertEquals([
            'amount' => '1',
            'currency' => 'BTC',
            'payment_method' => 'PAYMENT_METHOD_ID',
        ], $data);
    }

    public function testFromTransaction()
    {
        $transaction = new Transaction(TransactionType::SEND);
        $transaction->setTo(new Email('test@example.com'));
        $transaction->setAmount(new Money(1, CurrencyCode::BTC));
        $transaction->setDescription('test description');

        $data = $this->mapper->fromTransaction($transaction);

        $this->assertEquals([
            'type' => 'send',
            'amount' => '1',
            'currency' => 'BTC',
            'description' => 'test description',
            'to' => 'test@example.com',
        ], $data);
    }

    public function testFromSell()
    {
        $sell = new Sell();
        $sell->setAmount(new Money(1, CurrencyCode::BTC));
        $sell->setPaymentMethod(new PaymentMethod('PAYMENT_METHOD_ID'));

        $data = $this->mapper->fromSell($sell);

        $this->assertEquals([
            'amount' => '1',
            'currency' => 'BTC',
            'payment_method' => 'PAYMENT_METHOD_ID',
        ], $data);
    }

    public function testFromDeposit()
    {
        $deposit = new Deposit();
        $deposit->setAmount(new Money(10, CurrencyCode::USD));
        $deposit->setPaymentMethod(new PaymentMethod('PAYMENT_METHOD_ID'));

        $data = $this->mapper->fromDeposit($deposit);

        $this->assertEquals([
            'amount' => '10',
            'currency' => 'USD',
            'payment_method' => 'PAYMENT_METHOD_ID',
        ], $data);
    }

    public function testFromWithdrawal()
    {
        $withdrawal = new Withdrawal();
        $withdrawal->setAmount(new Money(10, CurrencyCode::USD));
        $withdrawal->setPaymentMethod(new PaymentMethod('PAYMENT_METHOD_ID'));

        $data = $this->mapper->fromWithdrawal($withdrawal);

        $this->assertEquals([
            'amount' => '10',
            'currency' => 'USD',
            'payment_method' => 'PAYMENT_METHOD_ID',
        ], $data);
    }

    public function testResourceReference()
    {
        $response = $this->getMockResponse(['data' => self::$transaction]);
        $transaction = $this->mapper->toTransaction($response);
        $to = $transaction->getTo();
        $this->assertInstanceOf(User::class, $to);
        $this->assertFalse($to->isExpanded());
    }

    public function testResourceReferenceExpanded()
    {
        $data = self::$transaction;
        $data['to'] = self::$user;

        $response = $this->getMockResponse(['data' => $data]);
        $transaction = $this->mapper->toTransaction($response);
        $to = $transaction->getTo();
        $this->assertInstanceOf(User::class, $to);
        $this->assertTrue($to->isExpanded());
    }

    // private

    /** @return \PHPUnit_Framework_MockObject_MockObject|ResponseInterface */
    private function getMockResponse(array $data)
    {
        $response = $this->getMock(ResponseInterface::class);
        $stream = $this->getMock(StreamInterface::class);

        $response->expects($this->any())
            ->method('getBody')
            ->willReturn($stream);
        $stream->expects($this->any())
            ->method('__toString')
            ->willReturn(json_encode((object) $data));

        return $response;
    }

    private static $buy = [
        'id' => '67e0eaec-07d7-54c4-a72c-2e92826897df',
        'status' => 'completed',
        'payment_method' => [
            'id' => '83562370-3e5c-51db-87da-752af5ab9559',
            'resource' => 'payment_method',
            'resource_path' => '/v2/payment-methods/83562370-3e5c-51db-87da-752af5ab9559',
        ],
        'transaction' => [
            'id' => '441b9494-b3f0-5b98-b9b0-4d82c21c252a',
            'resource' => 'transaction',
            'resource_path' => '/v2/accounts/2bbf394c-193b-5b2a-9155-3b4732659ede/transactions/441b9494-b3f0-5b98-b9b0-4d82c21c252a',
        ],
        'amount' => ['amount' => '1.00000000', 'currency' => 'BTC'],
        'total' => ['amount' => '10.25', 'currency' => 'USD'],
        'subtotal' => ['amount' => '10.10', 'currency' => 'USD'],
        'created_at' => '2015-01-31T20:49:02Z',
        'updated_at' => '2015-02-11T16:54:02-08:00',
        'resource' => 'buy',
        'resource_path' => '/v2/accounts/2bbf394c-193b-5b2a-9155-3b4732659ede/buys/67e0eaec-07d7-54c4-a72c-2e92826897df',
        'committed' => true,
        'instant' => false,
        'fees' => [
            [
                'type' => 'coinbase',
                'amount' => ['amount' => '0.00', 'currency' => 'USD'],
            ],
            [
                'type' => 'bank',
                'amount' => ['amount' => '0.15', 'currency' => 'USD'],
            ],
        ],
        'payout_at' => '2015-02-18T16:54:00-08:00',
    ];

    private static $transaction = [
        'id' => '0ec2de93-7dae-5a50-8580-6445a08e4ae4',
        'type' => 'send',
        'status' => 'pending',
        'amount' => ['amount' => '-1.00000000', 'currency' => 'BTC',],
        'native_amount' => ['amount' => '-10.00', 'currency' => 'USD',],
        'description' => null,
        'created_at' => '2015-01-31T20:49:02Z',
        'updated_at' => '2015-01-31T20:49:02Z',
        'resource' => 'transaction',
        'resource_path' => '/v2/accounts/8fcd97cd-50ca-5803-8c27-1146e54b1c09/transactions/0ec2de93-7dae-5a50-8580-6445a08e4ae4',
        'network' => [
            'status' => 'unconfirmed',
            'hash' => 'a7e23afeccf863dc8359ba04d2b854eddb6dea6901643828fdb3aca53d8bf600',
        ],
        'to' => [
            'id' => '9d55bef5-47f1-5936-b771-b07c1d8140a2',
            'resource' => 'user',
            'resource_path' => '/v2/users/9d55bef5-47f1-5936-b771-b07c1d8140a2',
        ],
    ];

    private static $user = [
        'id' => '9d55bef5-47f1-5936-b771-b07c1d8140a2',
        'name' => 'James Smith',
        'username' => null,
        'profile_location' => null,
        'profile_bio' => null,
        'profile_url' => null,
        'avatar_url' => 'https://images.coinbase.com/avatar?h=KphlECxEemoPGv3xtMSxqG2Ud7gEzke9mh0Ff3ifsiu9ggPwStQLCCuQfk6N%0AyY1p&s=128',
        'resource' => 'user',
        'resource_path' => '/v2/users/9d55bef5-47f1-5936-b771-b07c1d8140a2',
    ];

    private static $notification = [
        "id"=> "6bf0ca21-0b2f-5e8a-b95e-7bd7eaccc338",
        "type"=> "wallet:buys:completed",
        "data"=> [
          "id"=> "67e0eaec-07d7-54c4-a72c-2e92826897df",
          "status"=> "completed",
          "payment_method"=> [
            "id"=> "83562370-3e5c-51db-87da-752af5ab9559",
            "resource"=> "payment_method",
            "resource_path"=> "/v2/payment-methods/83562370-3e5c-51db-87da-752af5ab9559"
          ],
          "transaction"=> [
            "id"=> "441b9494-b3f0-5b98-b9b0-4d82c21c252a",
            "resource"=> "transaction",
            "resource_path"=> "/v2/accounts/2bbf394c-193b-5b2a-9155-3b4732659ede/transactions/441b9494-b3f0-5b98-b9b0-4d82c21c252a"
          ],
          "amount"=> [
            "amount"=> "1.00000000",
            "currency"=> "BTC"
          ],
          "total"=> [
            "amount"=> "10.25",
            "currency"=> "USD"
          ],
          "subtotal"=> [
            "amount"=> "10.10",
            "currency"=> "USD"
          ],
          "created_at"=> "2015-01-31T20:49:02Z",
          "updated_at"=> "2015-02-11T16:54:02-08:00",
          "resource"=> "buy",
          "resource_path"=> "/v2/accounts/2bbf394c-193b-5b2a-9155-3b4732659ede/buys/67e0eaec-07d7-54c4-a72c-2e92826897df",
          "committed"=> true,
          "instant"=> false,
          "fees"=> [
            [
              "type"=> "coinbase",
              "amount"=> [
                "amount"=> "0.00",
                "currency"=> "USD"
              ]
            ],
            [
              "type"=> "bank",
              "amount"=> [
                "amount"=> "0.15",
                "currency"=> "USD"
              ]
            ]
          ],
          "payout_at"=> "2015-02-18T16:54:00-08:00"
        ],
        "user"=> [
          "id"=> "f01c821e-bb35-555f-a4da-548672963119",
          "resource"=> "user",
          "resource_path"=> "/v2/users/f01c821e-bb35-555f-a4da-548672963119"
        ],
        "account"=> [
          "id"=> "8d5f086c-d7d5-58ee-890e-c09b3d8d4434",
          "resource"=> "account",
          "resource_path"=> "/v2/accounts/8d5f086c-d7d5-58ee-890e-c09b3d8d4434"
        ],
        "delivery_attempts"=> 1,
        "created_at"=> "2015-11-10T19:15:06Z",
        "resource"=> "notification",
        "resource_path"=> "/v2/notifications/6bf0ca21-0b2f-5e8a-b95e-7bd7eaccc338"
    ];
}
