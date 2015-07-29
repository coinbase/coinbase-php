<?php

namespace Coinbase\Wallet\Tests\ActiveRecord;

use Coinbase\Wallet\ActiveRecord\ActiveRecordContext;
use Coinbase\Wallet\Client;
use Coinbase\Wallet\Resource\PaymentMethod;

class PaymentMethodActiveRecordTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client */
    private $client;

    /** @var PaymentMethod */
    private $paymentMethod;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        ActiveRecordContext::setClient($this->client);

        $this->paymentMethod = new PaymentMethod();
    }

    protected function tearDown()
    {
        $this->client = null;
        $this->paymentMethod = null;
    }

    /**
     * @dataProvider provideForMethodProxy
     */
    public function testMethodProxy($method, $clientMethod)
    {
        $this->client->expects($this->once())
            ->method($clientMethod)
            ->with($this->paymentMethod, []);

        $this->paymentMethod->$method();
    }

    public function provideForMethodProxy()
    {
        return [
            'refresh' => ['refresh', 'refreshPaymentMethod'],
        ];
    }
}
