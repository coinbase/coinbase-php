<?php

namespace Coinbase\Wallet\Tests\Resource;

use Coinbase\Wallet\Enum\ResourceType;
use Coinbase\Wallet\Resource\Address;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    public function testReference()
    {
        $address = Address::reference('ACCOUNT_ID', 'ADDRESS_ID');

        $this->assertEquals(ResourceType::ADDRESS, $address->getResourceType());
        $this->assertEquals('/v2/accounts/ACCOUNT_ID/addresses/ADDRESS_ID', $address->getResourcePath());
    }

    public function testSetName()
    {
        $address = new Address();
        $address->setName('NAME');
        $this->assertEquals('NAME', $address->getName());
    }

    public function testSetCallbackUrl()
    {
        $address = new Address();
        $address->setCallbackUrl('URL');
        $this->assertEquals('URL', $address->getCallbackUrl());
    }
}
