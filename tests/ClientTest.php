<?php

namespace Coinbase\Wallet\Tests;

use Coinbase\Wallet\Client;
use Coinbase\Wallet\HttpClient;
use Coinbase\Wallet\Mapper;
use Coinbase\Wallet\Resource\Account;
use Coinbase\Wallet\Resource\Address;
use Coinbase\Wallet\Resource\Buy;
use Coinbase\Wallet\Resource\CurrentUser;
use Coinbase\Wallet\Resource\Deposit;
use Coinbase\Wallet\Resource\PaymentMethod;
use Coinbase\Wallet\Resource\ResourceCollection;
use Coinbase\Wallet\Resource\Sell;
use Coinbase\Wallet\Resource\Transaction;
use Coinbase\Wallet\Resource\User;
use Coinbase\Wallet\Resource\Withdrawal;
use Psr\Http\Message\ResponseInterface;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|HttpClient */
    private $http;

    /** @var \PHPUnit_Framework_MockObject_MockObject|Mapper */
    private $mapper;

    /** @var Client */
    private $client;

    public static function setUpBeforeClass() {
        date_default_timezone_set('America/New_York');
    }

    protected function setUp()
    {
        $this->http = $this->getMockBuilder(HttpClient::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->mapper = $this->getMock(Mapper::class);
        $this->client = new Client($this->http, $this->mapper);
    }

    protected function tearDown()
    {
        $this->http = null;
        $this->mapper = null;
        $this->client = null;
    }

    public function testGetUser()
    {
        $expected = new User();
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/users/USER_ID', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toUser')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getUser('USER_ID', ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testGetCurrentUser()
    {
        $expected = new User();
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/user', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toUser')
            ->willReturn($expected);

        $actual = $this->client->getCurrentUser(['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testGetCurrentAuthorization()
    {
        $expected = ['key' => 'value'];
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/user/auth', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toData')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getCurrentAuthorization(['foo' => 'bar']);
        $this->assertEquals($expected, $actual);
    }

    public function testUpdateCurrentUser()
    {
        $user = new CurrentUser();
        $response = $this->getMock(ResponseInterface::class);

        $this->mapper->expects($this->any())
            ->method('fromCurrentUser')
            ->with($user)
            ->willReturn(['key' => 'value']);
        $this->http->expects($this->once())
            ->method('put')
            ->with('/v2/user', ['key' => 'value', 'foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->once())
            ->method('toUser');

        $this->client->updateCurrentUser($user, ['foo' => 'bar']);
    }

    public function testGetAccounts()
    {
        $response = $this->getMock(ResponseInterface::class);
        $expected = new ResourceCollection();

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/accounts', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toAccounts')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getAccounts(['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testLoadNextAccounts()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceCollection $accounts */
        $accounts = $this->getMock(ResourceCollection::class);
        $response = $this->getMock(ResponseInterface::class);
        $nextPage = new ResourceCollection();

        $accounts->expects($this->any())
            ->method('getNextUri')
            ->willReturn('/test/next/uri');
        $this->http->expects($this->any())
            ->method('get')
            ->with('/test/next/uri', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toAccounts')
            ->willReturn($nextPage);
        $accounts->expects($this->once())
            ->method('mergeNextPage')
            ->with($nextPage);

        $this->client->loadNextAccounts($accounts, ['foo' => 'bar']);
    }

    public function testGetAccount()
    {
        $expected = new Account();
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/accounts/ACCOUNT_ID', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toAccount')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getAccount('ACCOUNT_ID', ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testCreateAccount()
    {
        $account = new Account();
        $response = $this->getMock(ResponseInterface::class);

        $this->mapper->expects($this->any())
            ->method('fromAccount')
            ->with($account)
            ->willReturn(['key' => 'value']);
        $this->http->expects($this->once())
            ->method('post')
            ->with('/v2/accounts', ['key' => 'value', 'foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->once())
            ->method('toAccount')
            ->with($response, $account);

        $this->client->createAccount($account, ['foo' => 'bar']);
    }

    public function testSetPrimaryAccount()
    {
        $account = Account::reference('ACCOUNT_ID');
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->once())
            ->method('post')
            ->with('/v2/accounts/ACCOUNT_ID/primary', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->once())
            ->method('toAccount')
            ->with($response, $account);

        $this->client->setPrimaryAccount($account, ['foo' => 'bar']);
    }

    public function testUpdateAccount()
    {
        $account = Account::reference('ACCOUNT_ID');
        $response = $this->getMock(ResponseInterface::class);

        $this->mapper->expects($this->any())
            ->method('fromAccount')
            ->with($account)
            ->willReturn(['key' => 'value']);
        $this->http->expects($this->once())
            ->method('put')
            ->with('/v2/accounts/ACCOUNT_ID', ['key' => 'value', 'foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->once())
            ->method('toAccount')
            ->with($response, $account);

        $this->client->updateAccount($account, ['foo' => 'bar']);
    }

    public function testDeleteAccount()
    {
        $account = Account::reference('ACCOUNT_ID');

        $this->http->expects($this->once())
            ->method('delete')
            ->with('/v2/accounts/ACCOUNT_ID', ['foo' => 'bar']);

        $this->client->deleteAccount($account, ['foo' => 'bar']);
    }

    public function testGetAddresses()
    {
        $account = Account::reference('ACCOUNT_ID');
        $response = $this->getMock(ResponseInterface::class);
        $expected = new ResourceCollection();

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/accounts/ACCOUNT_ID/addresses', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toAddresses')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getAccountAddresses($account, ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testLoadNextAddresses()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceCollection $addresses */
        $addresses = $this->getMock(ResourceCollection::class);
        $response = $this->getMock(ResponseInterface::class);
        $nextPage = new ResourceCollection();

        $addresses->expects($this->any())
            ->method('getNextUri')
            ->willReturn('/test/next/uri');
        $this->http->expects($this->any())
            ->method('get')
            ->with('/test/next/uri', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toAddresses')
            ->willReturn($nextPage);
        $addresses->expects($this->once())
            ->method('mergeNextPage')
            ->with($nextPage);

        $this->client->loadNextAddresses($addresses, ['foo' => 'bar']);
    }

    public function testGetAddress()
    {
        $account = Account::reference('ACCOUNT_ID');
        $response = $this->getMock(ResponseInterface::class);
        $expected = new Address();

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/accounts/ACCOUNT_ID/addresses/ADDRESS_ID', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toAddress')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getAccountAddress($account, 'ADDRESS_ID', ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testGetAddressTransactions()
    {
        $address = Address::reference('ACCOUNT_ID', 'ADDRESS_ID');
        $expected = new ResourceCollection();
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/accounts/ACCOUNT_ID/addresses/ADDRESS_ID/transactions', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toTransactions')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getAddressTransactions($address, ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testCreateAddress()
    {
        $account = Account::reference('ACCOUNT_ID');
        $address = new Address();
        $response = $this->getMock(ResponseInterface::class);

        $this->mapper->expects($this->any())
            ->method('fromAddress')
            ->with($address)
            ->willReturn(['key' => 'value']);
        $this->http->expects($this->once())
            ->method('post')
            ->with('/v2/accounts/ACCOUNT_ID/addresses', ['key' => 'value', 'foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->once())
            ->method('toAddress')
            ->with($response, $address);

        $this->client->createAccountAddress($account, $address, ['foo' => 'bar']);
    }

    public function testGetTransactions()
    {
        $account = Account::reference('ACCOUNT_ID');
        $response = $this->getMock(ResponseInterface::class);
        $expected = new ResourceCollection();

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/accounts/ACCOUNT_ID/transactions', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toTransactions')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getAccountTransactions($account, ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testLoadNextTransactions()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceCollection $addresses */
        $addresses = $this->getMock(ResourceCollection::class);
        $response = $this->getMock(ResponseInterface::class);
        $nextPage = new ResourceCollection();

        $addresses->expects($this->any())
            ->method('getNextUri')
            ->willReturn('/test/next/uri');
        $this->http->expects($this->any())
            ->method('get')
            ->with('/test/next/uri', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toTransactions')
            ->willReturn($nextPage);
        $addresses->expects($this->once())
            ->method('mergeNextPage')
            ->with($nextPage);

        $this->client->loadNextTransactions($addresses, ['foo' => 'bar']);
    }

    public function testGetTransaction()
    {
        $account = Account::reference('ACCOUNT_ID');
        $expected = new Transaction();
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/accounts/ACCOUNT_ID/transactions/TRANSACTION_ID', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toTransaction')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getAccountTransaction($account, 'TRANSACTION_ID', ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testCreateTransaction()
    {
        $account = Account::reference('ACCOUNT_ID');
        $transaction = new Transaction();
        $response = $this->getMock(ResponseInterface::class);

        $this->mapper->expects($this->any())
            ->method('fromTransaction')
            ->with($transaction)
            ->willReturn(['key' => 'value']);
        $this->http->expects($this->once())
            ->method('post')
            ->with('/v2/accounts/ACCOUNT_ID/transactions', ['key' => 'value', 'foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->once())
            ->method('toTransaction')
            ->with($response, $transaction);

        $this->client->createAccountTransaction($account, $transaction, ['foo' => 'bar']);
    }

    public function testCompleteRequestTransaction()
    {
        $transaction = Transaction::reference('ACCOUNT_ID', 'TRANSACTION_ID');

        $this->http->expects($this->once())
            ->method('post')
            ->with('/v2/accounts/ACCOUNT_ID/transactions/TRANSACTION_ID/complete', ['foo' => 'bar']);

        $this->client->completeTransaction($transaction, ['foo' => 'bar']);
    }

    public function testResendRequestTransaction()
    {
        $transaction = Transaction::reference('ACCOUNT_ID', 'TRANSACTION_ID');

        $this->http->expects($this->once())
            ->method('post')
            ->with('/v2/accounts/ACCOUNT_ID/transactions/TRANSACTION_ID/resend', ['foo' => 'bar']);

        $this->client->resendTransaction($transaction, ['foo' => 'bar']);
    }

    public function testCancelRequestTransaction()
    {
        $transaction = Transaction::reference('ACCOUNT_ID', 'TRANSACTION_ID');

        $this->http->expects($this->once())
            ->method('delete')
            ->with('/v2/accounts/ACCOUNT_ID/transactions/TRANSACTION_ID', ['foo' => 'bar']);

        $this->client->cancelTransaction($transaction, ['foo' => 'bar']);
    }

    public function testGetBuys()
    {
        $account = Account::reference('ACCOUNT_ID');
        $expected = new ResourceCollection();
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/accounts/ACCOUNT_ID/buys', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toBuys')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getAccountBuys($account, ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testLoadNextBuys()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceCollection $buys */
        $buys = $this->getMock(ResourceCollection::class);
        $response = $this->getMock(ResponseInterface::class);
        $nextPage = new ResourceCollection();

        $buys->expects($this->any())
            ->method('getNextUri')
            ->willReturn('/test/next/uri');
        $this->http->expects($this->any())
            ->method('get')
            ->with('/test/next/uri', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toBuys')
            ->willReturn($nextPage);
        $buys->expects($this->once())
            ->method('mergeNextPage')
            ->with($nextPage);

        $this->client->loadNextBuys($buys, ['foo' => 'bar']);
    }

    public function testGetBuy()
    {
        $account = Account::reference('ACCOUNT_ID');
        $expected = new Buy();
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/accounts/ACCOUNT_ID/buys/BUY_ID', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toBuy')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getAccountBuy($account, 'BUY_ID', ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testCreateBuy()
    {
        $account = Account::reference('ACCOUNT_ID');
        $buy = new Buy();
        $response = $this->getMock(ResponseInterface::class);

        $this->mapper->expects($this->any())
            ->method('fromBuy')
            ->with($buy)
            ->willReturn(['key' => 'value']);
        $this->http->expects($this->once())
            ->method('post')
            ->with('/v2/accounts/ACCOUNT_ID/buys', ['key' => 'value', 'foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->once())
            ->method('toBuy')
            ->with($response, $buy);

        $this->client->createAccountBuy($account, $buy, ['foo' => 'bar']);
    }

    public function testCommitBuy()
    {
        $buy = Buy::reference('ACCOUNT_ID', 'BUY_ID');
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->once())
            ->method('post')
            ->with('/v2/accounts/ACCOUNT_ID/buys/BUY_ID/commit', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->once())
            ->method('toBuy')
            ->with($response, $buy);

        $this->client->commitBuy($buy, ['foo' => 'bar']);
    }

    public function testGetSells()
    {
        $account = Account::reference('ACCOUNT_ID');
        $expected = new ResourceCollection();
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/accounts/ACCOUNT_ID/sells', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toSells')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getSells($account, ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testLoadNextSells()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceCollection $sells */
        $sells = $this->getMock(ResourceCollection::class);
        $response = $this->getMock(ResponseInterface::class);
        $nextPage = new ResourceCollection();

        $sells->expects($this->any())
            ->method('getNextUri')
            ->willReturn('/test/next/uri');
        $this->http->expects($this->any())
            ->method('get')
            ->with('/test/next/uri', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toSells')
            ->willReturn($nextPage);
        $sells->expects($this->once())
            ->method('mergeNextPage')
            ->with($nextPage);

        $this->client->loadNextSells($sells, ['foo' => 'bar']);
    }

    public function testGetSell()
    {
        $account = Account::reference('ACCOUNT_ID');
        $expected = new Sell();
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/accounts/ACCOUNT_ID/sells/SELL_ID', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toSell')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getAccountSell($account, 'SELL_ID', ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testCreateSell()
    {
        $account = Account::reference('ACCOUNT_ID');
        $sell = new Sell();
        $response = $this->getMock(ResponseInterface::class);

        $this->mapper->expects($this->any())
            ->method('fromSell')
            ->with($sell)
            ->willReturn(['key' => 'value']);
        $this->http->expects($this->once())
            ->method('post')
            ->with('/v2/accounts/ACCOUNT_ID/sells', ['key' => 'value', 'foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->once())
            ->method('toSell')
            ->with($response, $sell);

        $this->client->createAccountSell($account, $sell, ['foo' => 'bar']);
    }

    public function testCommitSell()
    {
        $sell = Sell::reference('ACCOUNT_ID', 'SELL_ID');
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->once())
            ->method('post')
            ->with('/v2/accounts/ACCOUNT_ID/sells/SELL_ID/commit', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->once())
            ->method('toSell')
            ->with($response, $sell);

        $this->client->commitSell($sell, ['foo' => 'bar']);
    }

    public function testGetDeposits()
    {
        $account = Account::reference('ACCOUNT_ID');
        $expected = new ResourceCollection();
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/accounts/ACCOUNT_ID/deposits', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toDeposits')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getAccountDeposits($account, ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testLoadNextDeposits()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceCollection $deposits */
        $deposits = $this->getMock(ResourceCollection::class);
        $response = $this->getMock(ResponseInterface::class);
        $nextPage = new ResourceCollection();

        $deposits->expects($this->any())
            ->method('getNextUri')
            ->willReturn('/test/next/uri');
        $this->http->expects($this->any())
            ->method('get')
            ->with('/test/next/uri', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toDeposits')
            ->willReturn($nextPage);
        $deposits->expects($this->once())
            ->method('mergeNextPage')
            ->with($nextPage);

        $this->client->loadNextDeposits($deposits, ['foo' => 'bar']);
    }

    public function testGetDeposit()
    {
        $account = Account::reference('ACCOUNT_ID');
        $expected = new Deposit();
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/accounts/ACCOUNT_ID/deposits/DEPOSIT_ID', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toDeposit')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getAccountDeposit($account, 'DEPOSIT_ID', ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testCreateDeposit()
    {
        $account = Account::reference('ACCOUNT_ID');
        $deposit = new Deposit();
        $response = $this->getMock(ResponseInterface::class);

        $this->mapper->expects($this->any())
            ->method('fromDeposit')
            ->with($deposit)
            ->willReturn(['key' => 'value']);
        $this->http->expects($this->once())
            ->method('post')
            ->with('/v2/accounts/ACCOUNT_ID/deposits', ['key' => 'value', 'foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->once())
            ->method('toDeposit')
            ->with($response, $deposit);

        $this->client->createAccountDeposit($account, $deposit, ['foo' => 'bar']);
    }

    public function testCommitDeposit()
    {
        $deposit = Deposit::reference('ACCOUNT_ID', 'DEPOSIT_ID');
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->once())
            ->method('post')
            ->with('/v2/accounts/ACCOUNT_ID/deposits/DEPOSIT_ID/commit', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->once())
            ->method('toDeposit')
            ->with($response, $deposit);

        $this->client->commitDeposit($deposit, ['foo' => 'bar']);
    }

    public function testGetWithdrawals()
    {
        $account = Account::reference('ACCOUNT_ID');
        $expected = new ResourceCollection();
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/accounts/ACCOUNT_ID/withdrawals', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toWithdrawals')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getAccountWithdrawals($account, ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testLoadNextWithdrawals()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceCollection $withdrawals */
        $withdrawals = $this->getMock(ResourceCollection::class);
        $response = $this->getMock(ResponseInterface::class);
        $nextPage = new ResourceCollection();

        $withdrawals->expects($this->any())
            ->method('getNextUri')
            ->willReturn('/test/next/uri');
        $this->http->expects($this->any())
            ->method('get')
            ->with('/test/next/uri', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toWithdrawals')
            ->willReturn($nextPage);
        $withdrawals->expects($this->once())
            ->method('mergeNextPage')
            ->with($nextPage);

        $this->client->loadNextWithdrawals($withdrawals, ['foo' => 'bar']);
    }

    public function testGetWithdrawal()
    {
        $account = Account::reference('ACCOUNT_ID');
        $expected = new Withdrawal();
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/accounts/ACCOUNT_ID/withdrawals/WITHDRAWAL_ID', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toWithdrawal')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getAccountWithdrawal($account, 'WITHDRAWAL_ID', ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testCreateWithdrawal()
    {
        $account = Account::reference('ACCOUNT_ID');
        $withdrawal = new Withdrawal();
        $response = $this->getMock(ResponseInterface::class);

        $this->mapper->expects($this->any())
            ->method('fromWithdrawal')
            ->with($withdrawal)
            ->willReturn(['key' => 'value']);
        $this->http->expects($this->once())
            ->method('post')
            ->with('/v2/accounts/ACCOUNT_ID/withdrawals', ['key' => 'value', 'foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->once())
            ->method('toWithdrawal')
            ->with($response, $withdrawal);

        $this->client->createAccountWithdrawal($account, $withdrawal, ['foo' => 'bar']);
    }

    public function testCommitWithdrawal()
    {
        $withdrawal = Withdrawal::reference('ACCOUNT_ID', 'WITHDRAWAL_ID');
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->once())
            ->method('post')
            ->with('/v2/accounts/ACCOUNT_ID/withdrawals/WITHDRAWAL_ID/commit', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->once())
            ->method('toWithdrawal')
            ->with($response, $withdrawal);

        $this->client->commitWithdrawal($withdrawal, ['foo' => 'bar']);
    }

    public function testGetPaymentMethods()
    {
        $expected = new ResourceCollection();
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/payment-methods', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toPaymentMethods')
            ->willReturn($expected);

        $actual = $this->client->getPaymentMethods(['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testLoadNextPaymentMethods()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceCollection $paymentMethods */
        $paymentMethods = $this->getMock(ResourceCollection::class);
        $response = $this->getMock(ResponseInterface::class);
        $nextPage = new ResourceCollection();

        $paymentMethods->expects($this->any())
            ->method('getNextUri')
            ->willReturn('/test/next/uri');
        $this->http->expects($this->any())
            ->method('get')
            ->with('/test/next/uri', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toPaymentMethods')
            ->willReturn($nextPage);
        $paymentMethods->expects($this->once())
            ->method('mergeNextPage')
            ->with($nextPage);

        $this->client->loadNextPaymentMethods($paymentMethods, ['foo' => 'bar']);
    }

    public function testGetPaymentMethod()
    {
        $expected = new PaymentMethod();
        $response = $this->getMock(ResponseInterface::class);

        $this->http->expects($this->any())
            ->method('get')
            ->with('/v2/payment-methods/PAYMENT_METHOD_ID', ['foo' => 'bar'])
            ->willReturn($response);
        $this->mapper->expects($this->any())
            ->method('toPaymentMethod')
            ->with($response)
            ->willReturn($expected);

        $actual = $this->client->getPaymentMethod('PAYMENT_METHOD_ID', ['foo' => 'bar']);
        $this->assertSame($expected, $actual);
    }

    public function testParseNotification()
    {
        $body = '{"id":"e5852770-ca8b-51be-b839-0b3aedc62252","type":"wallet:orders:paid","data":{"id":"146ed8ac-64c1-541e-8692-f9fa3d3d64d5","code":"3RTHN8KO","type":"order","name":"asdfasdf","description":"asdfasdfasdfasdf","amount":{"amount":"1.00","currency":"USD"},"receipt_url":"https://www.coinbase.com/orders/9c704d8a66a2204624ae7c39709db5f8/receipt","resource":"order","resource_path":"/v2/orders/146ed8ac-64c1-541e-8692-f9fa3d3d64d5","status":"paid","bitcoin_amount":{"amount":"0.00221000","currency":"BTC"},"payout_amount":null,"bitcoin_address":"1CHtgEP9YeDkkUQrdkmpGXdAe2LzP1esSg","refund_address":"1fnRUd2e9xk7KYv5uD4tdnnRnpvSZrbBb","bitcoin_uri":"bitcoin:1CHtgEP9YeDkkUQrdkmpGXdAe2LzP1esSg?amount=0.00221\\u0026r=https://www.coinbase.com/r/5690c45f57e9cd1211000079","notifications_url":null,"paid_at":"2016-01-09T08:27:36Z","mispaid_at":null,"expires_at":"2016-01-09T08:42:11Z","metadata":{},"created_at":"2016-01-09T08:27:11Z","updated_at":"2016-01-09T08:27:36Z","customer_info":null,"transaction":{"id":"4fbfcd5f-0252-57ac-be80-6b2037bda1c5","resource":"transaction","resource_path":"/v2/accounts/0d4b7e7f-5da8-506d-b0e1-8f5945d9d7f3/transactions/4fbfcd5f-0252-57ac-be80-6b2037bda1c5"},"mispayments":[],"refunds":[]},"user":{"id":"7eee8527-3439-52d9-98d6-a04c0d0dc6ce","resource":"user","resource_path":"/v2/users/7eee8527-3439-52d9-98d6-a04c0d0dc6ce"},"account":{"id":"0d4b7e7f-5da8-506d-b0e1-8f5945d9d7f3","resource":"account","resource_path":"/v2/accounts/0d4b7e7f-5da8-506d-b0e1-8f5945d9d7f3"},"delivery_attempts":0,"created_at":"2016-01-09T08:27:36Z","resource":"notification","resource_path":"/v2/notifications/e5852770-ca8b-51be-b839-0b3aedc62252"}';
        $this->mapper->expects($this->any())
            ->method('injectNotification')
            ->with($this->any());
    }

    public function testVerifyCallback()
    {
        $body = '{"order":{"id":null,"created_at":null,"status":"completed","event":null,"total_btc":{"cents":100000000,"currency_iso":"BTC"},"total_native":{"cents":1000,"currency_iso":"USD"},"total_payout":{"cents":1000,"currency_iso":"USD"},"custom":"123456789","receive_address":"mzVoQenSY6RTBgBUcpSBTBAvUMNgGWxgJn","button":{"type":"buy_now","name":"Test Item","description":null,"id":null},"transaction":{"id":"53bdfe4d091c0d74a7000003","hash":"4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b","confirmations":0}}}';
        $signature = '6yQRl17CNj5YSHSpF+tLjb0vVsNVEv021Tyy1bTVEQ69SWlmhwmJYuMc7jiDyeW9TLy4vRqSh4g4YEyN8eoQIM57pMoNw6Lw6Oudubqwp+E3cKtLFxW0l18db3Z/vhxn5BScAutHWwT/XrmkCNaHyCsvOOGMekwrNO7mxX9QIx21FBaEejJeviSYrF8bG6MbmFEs2VGKSybf9YrElR8BxxNe/uNfCXN3P5tO8MgR5wlL3Kr4yq8e6i4WWJgD08IVTnrSnoZR6v8JkPA+fn7I0M6cy0Xzw3BRMJAvdQB97wkobu97gFqJFKsOH2u/JR1S/UNP26vL0mzuAVuKAUwlRn0SUhWEAgcM3X0UCtWLYfCIb5QqrSHwlp7lwOkVnFt329Mrpjy+jAfYYSRqzIsw4ZsRRVauy/v3CvmjPI9sUKiJ5l1FSgkpK2lkjhFgKB3WaYZWy9ZfIAI9bDyG8vSTT7IDurlUhyTweDqVNlYUsO6jaUa4KmSpg1o9eIeHxm0XBQ2c0Lv/T39KNc/VOAi1LBfPiQYMXD1e/8VuPPBTDGgzOMD3i334ppSr36+8YtApAn3D36Hr9jqAfFrugM7uPecjCGuleWsHFyNnJErT0/amIt24Nh1GoiESEq42o7Co4wZieKZ+/yeAlIUErJzK41ACVGmTnGoDUwEBXxADOdA=';
        $this->assertTrue($this->client->verifyCallback($body, $signature));
    }

    public function testVerifyCallbackFailure()
    {
        $body = '{"order":{"id":null,"created_at":null,"status":"completed","event":null,"total_btc":{"cents":1000000000,"currency_iso":"BTC"},"total_native":{"cents":1000,"currency_iso":"USD"},"total_payout":{"cents":1000,"currency_iso":"USD"},"custom":"123456789","receive_address":"mzVoQenSY6RTBgBUcpSBTBAvUMNgGWxgJn","button":{"type":"buy_now","name":"Test Item","description":null,"id":null},"transaction":{"id":"53bdfe4d091c0d74a7000003","hash":"4a5e1e4baab89f3a32518a88c31bc87f618f76673e2cc77ab2127b7afdeda33b","confirmations":0}}}';
        $signature = '6yQRl17CNj5YSHSpF+tLjb0vVsNVEv021Tyy1bTVEQ69SWlmhwmJYuMc7jiDyeW9TLy4vRqSh4g4YEyN8eoQIM57pMoNw6Lw6Oudubqwp+E3cKtLFxW0l18db3Z/vhxn5BScAutHWwT/XrmkCNaHyCsvOOGMekwrNO7mxX9QIx21FBaEejJeviSYrF8bG6MbmFEs2VGKSybf9YrElR8BxxNe/uNfCXN3P5tO8MgR5wlL3Kr4yq8e6i4WWJgD08IVTnrSnoZR6v8JkPA+fn7I0M6cy0Xzw3BRMJAvdQB97wkobu97gFqJFKsOH2u/JR1S/UNP26vL0mzuAVuKAUwlRn0SUhWEAgcM3X0UCtWLYfCIb5QqrSHwlp7lwOkVnFt329Mrpjy+jAfYYSRqzIsw4ZsRRVauy/v3CvmjPI9sUKiJ5l1FSgkpK2lkjhFgKB3WaYZWy9ZfIAI9bDyG8vSTT7IDurlUhyTweDqVNlYUsO6jaUa4KmSpg1o9eIeHxm0XBQ2c0Lv/T39KNc/VOAi1LBfPiQYMXD1e/8VuPPBTDGgzOMD3i334ppSr36+8YtApAn3D36Hr9jqAfFrugM7uPecjCGuleWsHFyNnJErT0/amIt24Nh1GoiESEq42o7Co4wZieKZ+/yeAlIUErJzK41ACVGmTnGoDUwEBXxADOdA=';
        $this->assertFalse($this->client->verifyCallback($body, $signature));
    }
}
