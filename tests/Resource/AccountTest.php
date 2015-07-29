<?php

namespace Coinbase\Wallet\Tests\Resource;

use Coinbase\Wallet\Enum\ResourceType;
use Coinbase\Wallet\Resource\Account;

class AccountTest extends \PHPUnit_Framework_TestCase
{
    public function testReference()
    {
        $account = Account::reference('ACCOUNT_ID');

        $this->assertEquals(ResourceType::ACCOUNT, $account->getResourceType());
        $this->assertEquals('/v2/accounts/ACCOUNT_ID', $account->getResourcePath());
        $this->assertEquals('ACCOUNT_ID', $account->getId());
    }

    public function testSetName()
    {
        $account = new Account();
        $account->setName('NAME');
        $this->assertEquals('NAME', $account->getName());
    }
}
