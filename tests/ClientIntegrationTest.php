<?php

namespace Coinbase\Wallet\Tests;

use Coinbase\Wallet\Client;
use Coinbase\Wallet\Configuration;
use Coinbase\Wallet\Exception\ExpiredTokenException;
use Coinbase\Wallet\Exception\HttpException;
use Coinbase\Wallet\Exception\InvalidRequestException;
use Coinbase\Wallet\Exception\InvalidTokenException;
use Coinbase\Wallet\Exception\RevokedTokenException;
use Coinbase\Wallet\Resource\Account;
use Coinbase\Wallet\Resource\Address;
use Coinbase\Wallet\Resource\Notification;
use Coinbase\Wallet\Resource\CurrentUser;
use Coinbase\Wallet\Resource\PaymentMethod;
use Coinbase\Wallet\Resource\ResourceCollection;
use Coinbase\Wallet\Resource\User;
use Coinbase\Wallet\Value\Money;

/**
 * @group integration
 */
class ClientIntegrationTest extends \PHPUnit_Framework_TestCase
{
    /** @var Client */
    private $client;
    private $accounts = [];

    public static function setUpBeforeClass()
    {
        if (!isset($_SERVER['CB_API_KEY']) || !isset($_SERVER['CB_API_SECRET'])) {
            self::markTestSkipped(
                'Environment variables CB_API_KEY and/or CB_API_SECRET are missing'
            );
        }

        date_default_timezone_set('America/New_York');
    }

    protected function setUp()
    {
        $configuration = Configuration::apiKey(
            $_SERVER['CB_API_KEY'],
            $_SERVER['CB_API_SECRET']
        );

        $this->client = Client::create($configuration);
    }

    protected function tearDown()
    {
        while ($account = array_pop($this->accounts)) {
            try {
                $this->client->deleteAccount($account);
            } catch (HttpException $e) {
                // pass
            }
        }

        $this->client = null;
    }

    public function testOAuthAuthentication()
    {
        if (!isset($_SERVER['CB_OAUTH_ACCESS_TOKEN'])) {
            $this->markTestSkipped('Environment variable CB_OAUTH_ACCESS_TOKEN is missing');
        }

        $configuration = Configuration::oauth($_SERVER['CB_OAUTH_ACCESS_TOKEN']);
        $client = Client::create($configuration);

        try {
            $user = $client->getCurrentUser();
            $this->assertInstanceOf(CurrentUser::class, $user);
        } catch (ExpiredTokenException $e) {
            $this->markTestSkipped('The OAuth token has expired');
        } catch (InvalidTokenException $e) {
            $this->markTestSkipped('The OAuth token is invalid');
        } catch (RevokedTokenException $e) {
            $this->markTestSkipped('The OAuth token is revoked');
        }
    }

    public function testOAuthRefreshToken()
    {
        if (!isset($_SERVER['CB_OAUTH_ACCESS_TOKEN']) || !isset($_SERVER['CB_OAUTH_REFRESH_TOKEN'])) {
            $this->markTestSkipped('Environment variables CB_OAUTH_ACCESS_TOKEN and/or CB_OAUTH_REFRESH_TOKEN are missing');
        }

        $configuration = Configuration::oauth(
            $_SERVER['CB_OAUTH_ACCESS_TOKEN'],
            $_SERVER['CB_OAUTH_REFRESH_TOKEN']
        );

        $client = Client::create($configuration);

        try {
            $client->refreshAuthentication();
        } catch (InvalidRequestException $e) {
            $this->markTestSkipped('The OAuth token is invalid');
        }
    }

    public function testGetCurrencies()
    {
        $data = $this->client->getCurrencies();

        $this->assertInternalType('array', $data);
    }

    public function testGetExchangeRates()
    {
        $data = $this->client->getExchangeRates('CAD');

        $this->assertInternalType('array', $data);
        $this->assertEquals('CAD', $data['currency']);
    }

    public function testGetBuyPrice1()
    {
        $price = $this->client->getBuyPrice();

        $this->assertInstanceOf(Money::class, $price);
    }

    public function testGetBuyPrice2()
    {
        $price = $this->client->getBuyPrice('USD');

        $this->assertInstanceOf(Money::class, $price);
    }

    public function testGetBuyPrice3()
    {
        $price = $this->client->getBuyPrice('ETH-USD');

        $this->assertInstanceOf(Money::class, $price);
    }

    public function testGetSellPrice1()
    {
        $price = $this->client->getSellPrice();

        $this->assertInstanceOf(Money::class, $price);
    }

    public function testGetSellPrice2()
    {
        $price = $this->client->getSellPrice('USD');

        $this->assertInstanceOf(Money::class, $price);
    }

    public function testGetSellPrice3()
    {
        $price = $this->client->getSellPrice('ETH-USD');

        $this->assertInstanceOf(Money::class, $price);
    }

    public function testGetSpotPrice1()
    {
        $price = $this->client->getSpotPrice();

        $this->assertInstanceOf(Money::class, $price);
    }

    public function testGetSpotPrice2()
    {
        $price = $this->client->getSpotPrice('USD');

        $this->assertInstanceOf(Money::class, $price);
    }

    public function testGetSpotPrice3()
    {
        $price = $this->client->getSpotPrice('ETH-USD');

        $this->assertInstanceOf(Money::class, $price);
    }

    public function testGetCurrentUser()
    {
        $user = $this->client->getCurrentUser();
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotEmpty($user->getId());
    }

    public function testGetUser()
    {
        $user = $this->client->getCurrentUser();
        $user = $this->client->getUser($user->getId());
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotEmpty($user->getId());
    }

    public function testUpdateCurrentUser()
    {
        $user = $this->client->getCurrentUser();
        $user->setName('John Doe' === $user->getName() ? 'Jane Doe' : 'John Doe');
        $this->client->updateCurrentUser($user);
    }

    public function testCreateAccountInvalid()
    {
        $account = new Account();

        try {
            $this->client->createAccount($account);
            $this->accounts[] = $account;
            $this->fail();
        } catch (HttpException $e) {
            $this->assertNotEmpty($e->getErrors());
        }
    }

    public function testCreateAccount()
    {
        $account = $this->createAccount();
        $this->assertNotEmpty($account->getId());
    }

    public function testSetPrimaryAccount()
    {
        $this->accounts[] = $this->client->getPrimaryAccount();

        $account = $this->createAccount();
        $this->client->setPrimaryAccount($account);
        $this->assertTrue($account->isPrimary());
    }

    public function testUpdateAccount()
    {
        $account = $this->createAccount();
        $account->setName('foo');
        $this->client->updateAccount($account);

        $account->setName('bar');
        $this->client->refreshAccount($account);
        $this->assertEquals('foo', $account->getName());
    }

    public function testLoadNextAccounts()
    {
        $this->createAccount();
        $this->createAccount();

        $accounts = $this->client->getAccounts(['limit' => 1]);
        $this->assertCount(1, $accounts);

        $this->client->loadNextAccounts($accounts, ['limit' => 1]);
        $this->assertCount(2, $accounts);
    }

    public function testDeleteAccount()
    {
        $account = $this->createAccount();
        $this->client->deleteAccount($account);
    }

    public function testCreateAddress()
    {
        $account = $this->createAccount();
        $address = new Address();
        $this->client->createAccountAddress($account, $address);
        $this->assertNotEmpty($address->getId());
    }

    public function testLoadNextAddresses()
    {
        $account = $this->createAccount();
        $this->client->createAccountAddress($account, new Address());
        sleep(1);
        $this->client->createAccountAddress($account, new Address());
        sleep(1);

        $addresses = $this->client->getAccountAddresses($account, ['limit' => 1]);
        $this->assertCount(1, $addresses);

        $this->client->loadNextAddresses($addresses, ['limit' => 1]);
        $this->assertCount(2, $addresses);
    }

    public function testRefreshAddress()
    {
        $account = $this->createAccount();
        $address = new Address();
        $address->setName('foo');
        $this->client->createAccountAddress($account, $address);

        $address->setName('bar');
        $this->client->refreshAddress($address);

        $this->assertEquals('foo', $address->getName());
    }

    public function testGetAddressTransactions()
    {
        $account = $this->createAccount();
        $address = new Address();
        $address->setName('foo');
        $this->client->createAccountAddress($account, $address);

        $transactions = $this->client->getAddressTransactions($address);
        $this->assertEmpty($transactions);
    }

    public function testGetAccountTransactions()
    {
        $account      = $this->client->getPrimaryAccount();
        $transactions = $this->client->getAccountTransactions($account);
    }

    public function testGetPaymentMethods()
    {
        $paymentMethods = $this->client->getPaymentMethods();
        $this->assertInstanceOf(ResourceCollection::class, $paymentMethods);

        if (!isset($paymentMethods[0])) {
            $this->markTestSkipped('User has no payment methods');
        }

        $this->assertInstanceOf(PaymentMethod::class, $paymentMethods[0]);
    }

    public function testGetHistoricPrices() {
        $historicPrices = $this->client->getHistoricPrices('CAD');

        $this->assertEquals("array", gettype($historicPrices));
        $this->assertEquals('CAD', $historicPrices['currency']);
        $this->assertEquals(365, sizeof($historicPrices['prices']));
    }

    public function testGetNotifications() {
        $notifications = $this->client->getNotifications();
        $this->assertInstanceOf(ResourceCollection::class, $notifications);

        if (!isset($notifications[0])) {
            $this->markTestSkipped('User has no notifications');
        }

        $this->assertInstanceOf(Notification::class, $notifications[0]);
    }

    // private

    private function createAccount()
    {
        $this->accounts[] = $account = new Account();
        $account->setName('test'.time());
        $this->client->createAccount($account);

        return $account;
    }
}
