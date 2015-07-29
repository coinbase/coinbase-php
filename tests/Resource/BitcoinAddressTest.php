<?php

namespace Coinbase\Wallet\Tests\Resource;

use Coinbase\Wallet\Enum\ResourceType;
use Coinbase\Wallet\Resource\BitcoinAddress;

class BitcoinAddressTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $address = new BitcoinAddress('ADDRESS');

        $this->assertEquals(ResourceType::BITCOIN_ADDRESS, $address->getResourceType());
        $this->assertNull($address->getId());
        $this->assertNull($address->getResourcePath());
        $this->assertNull($address->getRawData());
        $this->assertEquals('ADDRESS', $address->getAddress());
    }
}
