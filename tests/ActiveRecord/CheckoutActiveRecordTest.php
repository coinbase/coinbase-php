<?php

namespace Coinbase\Wallet\Tests\ActiveRecord;

use Coinbase\Wallet\ActiveRecord\ActiveRecordContext;
use Coinbase\Wallet\Client;
use Coinbase\Wallet\Resource\Checkout;

class CheckoutActiveRecordTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client */
    private $client;

    /** @var Checkout */
    private $checkout;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        ActiveRecordContext::setClient($this->client);

        $this->checkout = new Checkout();
    }

    protected function tearDown()
    {
        $this->client = null;
        $this->checkout = null;
    }

    /**
     * @dataProvider provideForMethodProxy
     */
    public function testMethodProxy($method, $clientMethod)
    {
        $this->client->expects($this->once())
            ->method($clientMethod)
            ->with($this->checkout, []);

        $this->checkout->$method();
    }

    public function provideForMethodProxy()
    {
        return [
            'refresh'        => ['refresh', 'refreshCheckout'],
            'getOrders'      => ['getOrders', 'getCheckoutOrders'],
            'createNewOrder' => ['createNewOrder', 'createNewCheckoutOrder'],
        ];
    }
}
