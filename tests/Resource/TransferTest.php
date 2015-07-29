<?php

namespace Coinbase\Wallet\Tests\Resource;

use Coinbase\Wallet\Enum\CurrencyCode;
use Coinbase\Wallet\Resource\PaymentMethod;
use Coinbase\Wallet\Value\Money;

class TransferTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $transfer = new TransferStub('/v2/accounts/ACCOUNT_ID/buys/BUY_ID');
        $this->assertEquals('ACCOUNT_ID', $transfer->getAccountId());
        $this->assertEquals('BUY_ID', $transfer->getId());
    }

    public function testConstructorEmpty()
    {
        $transfer = new TransferStub();
        $this->assertNull($transfer->getAccountId());
        $this->assertNull($transfer->getId());
    }

    public function testSetPaymentMethodId()
    {
        $transfer = new TransferStub();
        $transfer->setPaymentMethodId('PAYMENT_METHOD_ID');

        $this->assertInstanceOf(PaymentMethod::class, $transfer->getPaymentMethod());
        $this->assertEquals('PAYMENT_METHOD_ID', $transfer->getPaymentMethod()->getId());
    }

    public function testSetBitcoinAmount()
    {
        $transfer = new TransferStub();
        $transfer->setBitcoinAmount(1);

        $this->assertInstanceOf(Money::class, $transfer->getAmount());
        $this->assertEquals(1, $transfer->getAmount()->getAmount());
        $this->assertEquals(CurrencyCode::BTC, $transfer->getAmount()->getCurrency());
    }
}
