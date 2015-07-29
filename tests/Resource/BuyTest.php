<?php

namespace Coinbase\Wallet\Tests\Resource;

use Coinbase\Wallet\Enum\CurrencyCode;
use Coinbase\Wallet\Enum\ResourceType;
use Coinbase\Wallet\Resource\Buy;
use Coinbase\Wallet\Value\Money;

class BuyTest extends \PHPUnit_Framework_TestCase
{
    public function testReference()
    {
        $buy = Buy::reference('ACCOUNT_ID', 'BUY_ID');

        $this->assertEquals(ResourceType::BUY, $buy->getResourceType());
        $this->assertEquals('/v2/accounts/ACCOUNT_ID/buys/BUY_ID', $buy->getResourcePath());
        $this->assertEquals('BUY_ID', $buy->getId());
        $this->assertEquals('ACCOUNT_ID', $buy->getAccountId());

    }

    public function testSetTotal()
    {
        $expected = new Money(10, CurrencyCode::USD);

        $buy = new Buy();
        $buy->setTotal($expected);

        $this->assertSame($expected, $buy->getTotal());
    }
}
