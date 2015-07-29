<?php

namespace Coinbase\Wallet\Tests\Resource;

use Coinbase\Wallet\Enum\ResourceType;
use Coinbase\Wallet\Resource\Order;
use Coinbase\Wallet\Value\Money;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    public function testReference()
    {
        $order = Order::reference('ORDER_ID');

        $this->assertEquals(ResourceType::ORDER, $order->getResourceType());
        $this->assertEquals('/v2/orders/ORDER_ID', $order->getResourcePath());
        $this->assertEquals('ORDER_ID', $order->getId());
    }

    public function testSetName()
    {
        $order = new Order();
        $order->setName('NAME');
        $this->assertEquals('NAME', $order->getName());
    }

    public function testSetDescription()
    {
        $order = new Order();
        $order->setDescription('DESCRIPTION');
        $this->assertEquals('DESCRIPTION', $order->getDescription());
    }

    public function testSetAmount()
    {
        $amount = $this->getMockBuilder(Money::class)
            ->disableOriginalConstructor()
            ->getMock();

        $order = new Order();
        $order->setAmount($amount);
        $this->assertSame($amount, $order->getAmount());
    }

    public function testSetMetadata()
    {
        $order = new Order();
        $order->setMetadata(['FOO' => 'BAR']);
        $this->assertEquals(['FOO' => 'BAR'], $order->getMetadata());
    }
}
