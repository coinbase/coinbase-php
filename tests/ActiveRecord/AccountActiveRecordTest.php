<?php

namespace Coinbase\Wallet\Tests\ActiveRecord;

use Coinbase\Wallet\ActiveRecord\ActiveRecordContext;
use Coinbase\Wallet\Client;
use Coinbase\Wallet\Resource\Account;
use Coinbase\Wallet\Resource\Address;
use Coinbase\Wallet\Resource\Buy;
use Coinbase\Wallet\Resource\Deposit;
use Coinbase\Wallet\Resource\Sell;
use Coinbase\Wallet\Resource\Transaction;
use Coinbase\Wallet\Resource\Withdrawal;

class AccountActiveRecordTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client */
    private $client;

    /** @var Account */
    private $account;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        ActiveRecordContext::setClient($this->client);

        $this->account = new Account();
    }

    protected function tearDown()
    {
        $this->client = null;
        $this->account = null;
    }

    /**
     * @dataProvider provideForMethodProxy
     */
    public function testMethodProxy($method, $clientMethod)
    {
        $this->client->expects($this->once())
            ->method($clientMethod)
            ->with($this->account, []);

        $this->account->$method();
    }

    public function provideForMethodProxy()
    {
        return [
            'refresh'         => ['refresh', 'refreshAccount'],
            'update'          => ['update', 'updateAccount'],
            'makePrimary'     => ['makePrimary', 'setPrimaryAccount'],
            'delete'          => ['delete', 'deleteAccount'],
            'getAddresses'    => ['getAddresses', 'getAccountAddresses'],
            'getTransactions' => ['getTransactions', 'getAccountTransactions'],
            'getBuys'         => ['getBuys', 'getAccountBuys'],
            'getSells'        => ['getSells', 'getSells'],
            'getDeposits'     => ['getDeposits', 'getAccountDeposits'],
            'getWithdrawals'  => ['getWithdrawals', 'getAccountWithdrawals'],
        ];
    }

    public function testGetAddress()
    {
        $expected = new Address();

        $this->client->expects($this->any())
            ->method('getAccountAddress')
            ->with($this->account, 'ID', [])
            ->willReturn($expected);

        $actual = $this->account->getAddress('ID');
        $this->assertSame($expected, $actual);
    }

    public function testCreateAddress()
    {
        $address = new Address();

        $this->client->expects($this->once())
            ->method('createAccountAddress')
            ->with($this->account, $address, []);

        $this->account->createAddress($address);
    }

    public function testGetTransaction()
    {
        $expected = new Transaction();

        $this->client->expects($this->any())
            ->method('getAccountTransaction')
            ->with($this->account, 'ID', [])
            ->willReturn($expected);

        $actual = $this->account->getTransaction('ID');
        $this->assertSame($expected, $actual);
    }

    public function testCreateTransaction()
    {
        $transaction = new Transaction();

        $this->client->expects($this->once())
            ->method('createAccountTransaction')
            ->with($this->account, $transaction, []);

        $this->account->createTransaction($transaction);
    }

    public function testGetBuy()
    {
        $expected = new Buy();

        $this->client->expects($this->any())
            ->method('getAccountBuy')
            ->with($this->account, 'ID', [])
            ->willReturn($expected);

        $actual = $this->account->getBuy('ID');
        $this->assertSame($expected, $actual);
    }

    public function testCreateBuy()
    {
        $buy = new Buy();

        $this->client->expects($this->once())
            ->method('createAccountBuy')
            ->with($this->account, $buy, []);

        $this->account->createBuy($buy);
    }

    public function testCommitBuy()
    {
        $buy = new Buy();

        $this->client->expects($this->once())
            ->method('commitBuy')
            ->with($buy, []);

        $this->account->commitBuy($buy);
    }

    public function testGetSell()
    {
        $expected = new Sell();

        $this->client->expects($this->any())
            ->method('getAccountSell')
            ->with($this->account, 'ID', [])
            ->willReturn($expected);

        $actual = $this->account->getSell('ID');
        $this->assertSame($expected, $actual);
    }

    public function testCreateSell()
    {
        $sell = new Sell();

        $this->client->expects($this->once())
            ->method('createAccountSell')
            ->with($this->account, $sell, []);

        $this->account->createSell($sell);
    }

    public function testCommitSell()
    {
        $sell = new Sell();

        $this->client->expects($this->once())
            ->method('commitSell')
            ->with($sell, []);

        $this->account->commitSell($sell);
    }

    public function testGetDeposit()
    {
        $expected = new Deposit();

        $this->client->expects($this->any())
            ->method('getAccountDeposit')
            ->with($this->account, 'ID', [])
            ->willReturn($expected);

        $actual = $this->account->getDeposit('ID');
        $this->assertSame($expected, $actual);
    }

    public function testCreateDeposit()
    {
        $deposit = new Deposit();

        $this->client->expects($this->once())
            ->method('createAccountDeposit')
            ->with($this->account, $deposit, []);

        $this->account->createDeposit($deposit);
    }

    public function testCommitDeposit()
    {
        $deposit = new Deposit();

        $this->client->expects($this->once())
            ->method('commitDeposit')
            ->with($deposit, []);

        $this->account->commitDeposit($deposit);
    }

    public function testGetWithdrawal()
    {
        $expected = new Withdrawal();

        $this->client->expects($this->any())
            ->method('getAccountWithdrawal')
            ->with($this->account, 'ID', [])
            ->willReturn($expected);

        $actual = $this->account->getWithdrawal('ID');
        $this->assertSame($expected, $actual);
    }

    public function testCreateWithdrawal()
    {
        $withdrawal = new Withdrawal();

        $this->client->expects($this->once())
            ->method('createAccountWithdrawal')
            ->with($this->account, $withdrawal, []);

        $this->account->createWithdrawal($withdrawal);
    }

    public function testCommitWithdrawal()
    {
        $withdrawal = new Withdrawal();

        $this->client->expects($this->once())
            ->method('commitWithdrawal')
            ->with($withdrawal, []);

        $this->account->commitWithdrawal($withdrawal);
    }
}
