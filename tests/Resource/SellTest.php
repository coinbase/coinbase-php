<?php

namespace Coinbase\Wallet\Tests\Resource;

use Coinbase\Wallet\Enum\CurrencyCode;
use Coinbase\Wallet\Resource\Sell;
use Coinbase\Wallet\Value\Money;

class SellTest extends \PHPUnit_Framework_TestCase
{
    public function testSetTotal()
    {
        $expected = new Money(10, CurrencyCode::USD);

        $sell = new Sell();
        $sell->setTotal($expected);

        $this->assertSame($expected, $sell->getTotal());
    }
}
