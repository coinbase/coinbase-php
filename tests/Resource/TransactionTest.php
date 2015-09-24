<?php

namespace Coinbase\Wallet\Tests\Resource;

use Coinbase\Wallet\Enum\CurrencyCode;
use Coinbase\Wallet\Enum\TransactionType;
use Coinbase\Wallet\Resource\Account;
use Coinbase\Wallet\Resource\BitcoinAddress;
use Coinbase\Wallet\Resource\Email;
use Coinbase\Wallet\Resource\Transaction;
use Coinbase\Wallet\Value\Money;

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    public function testSend()
    {
        $transaction = Transaction::send();
        $this->assertEquals(TransactionType::SEND, $transaction->getType());
    }

    public function testSendWithAttrs()
    {
        $to = new Email('test@example.com');
        $amount = new Money(1, CurrencyCode::BTC);
        $transaction = Transaction::send(array(
            'to'     => $to,
            'amount' => $amount
        ));
        $this->assertEquals(TransactionType::SEND, $transaction->getType());
        $this->assertEquals($amount, $transaction->getAmount());
        $this->assertEquals($to, $transaction->getTo());
    }

    public function testSendWithAttrs2()
    {
        $transaction = Transaction::send([
            'toEmail' => 'test@example.com',
            'bitcoinAmount' => 1
        ]);

        $this->assertEquals(new Money(1, CurrencyCode::BTC), $transaction->getAmount());
        $this->assertEquals(new Email('test@example.com'), $transaction->getTo());
    }

    public function testTransfer()
    {
        $transaction = Transaction::transfer();
        $this->assertEquals(TransactionType::TRANSFER, $transaction->getType());
    }

    public function testRequest()
    {
        $transaction = Transaction::request();
        $this->assertEquals(TransactionType::REQUEST, $transaction->getType());
    }

    public function testSetAmount()
    {
        $expected = new Money(1, CurrencyCode::BTC);

        $transaction = new Transaction();
        $transaction->setAmount($expected);

        $this->assertEquals($expected, $transaction->getAmount());
    }

    public function testSetBitcoinAmount()
    {
        $transaction = new Transaction();
        $transaction->setBitcoinAmount(1);

        $this->assertInstanceOf(Money::class, $transaction->getAmount());
        $this->assertEquals(1, $transaction->getAmount()->getAmount());
        $this->assertEquals(CurrencyCode::BTC, $transaction->getAmount()->getCurrency());
    }

    public function testSetDescription()
    {
        $transaction = new Transaction();
        $transaction->setDescription('DESCRIPTION');
        $this->assertEquals('DESCRIPTION', $transaction->getDescription());
    }

    public function testSetToAccountId()
    {
        $transaction = new Transaction();
        $transaction->setToAccountId('ACCOUNT_ID');

        $this->assertInstanceOf(Account::class, $transaction->getTo());
        $this->assertEquals('ACCOUNT_ID', $transaction->getTo()->getId());
    }

    public function testSetToEmail()
    {
        $transaction = new Transaction();
        $transaction->setToEmail('test@example.com');

        $this->assertInstanceOf(Email::class, $transaction->getTo());
        $this->assertEquals('test@example.com', $transaction->getTo()->getEmail());
    }

    public function testSetToBitcoinAddress()
    {
        $transaction = new Transaction();
        $transaction->setToBitcoinAddress('ADDRESS');

        $this->assertInstanceOf(BitcoinAddress::class, $transaction->getTo());
        $this->assertEquals('ADDRESS', $transaction->getTo()->getAddress());
    }
}
