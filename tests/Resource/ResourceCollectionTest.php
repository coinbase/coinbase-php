<?php

namespace Coinbase\Wallet\Tests\Resource;

use Coinbase\Wallet\Resource\Resource;
use Coinbase\Wallet\Resource\ResourceCollection;

class ResourceCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetFirstId()
    {
        $coll = new ResourceCollection();
        $coll->add(new Resource('test', 'FIRST_ID'));
        $coll->add(new Resource('test', 'LAST_ID'));
        $this->assertEquals('FIRST_ID', $coll->getFirstId());
    }

    public function testGetFirstIdEmptyCollection()
    {
        $coll = new ResourceCollection();
        $this->assertNull($coll->getFirstId());
    }

    public function testGetLastId()
    {
        $coll = new ResourceCollection();
        $coll->add(new Resource('test', 'FIRST_ID'));
        $coll->add(new Resource('test', 'LAST_ID'));
        $this->assertEquals('LAST_ID', $coll->getLastId());
    }

    public function testGetLastIdEmptyCollection()
    {
        $coll = new ResourceCollection();
        $this->assertNull($coll->getLastId());
    }
}
