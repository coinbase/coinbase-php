<?php

namespace Coinbase\Wallet\Tests\Resource;

class AccountResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testGetAccountId()
    {
        $resource = new AccountResourceStub('/v2/accounts/ACCOUNT_ID/widgets/WIDGET_ID');

        $this->assertEquals('ACCOUNT_ID', $resource->getAccountId());
        $this->assertEquals('WIDGET_ID', $resource->getId());
    }
}
