<?php

namespace Coinbase\Wallet\Tests\Resource;

use Coinbase\Wallet\Resource\Resource;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorId()
    {
        $resource = new Resource(null, '/v2/widgets/WIDGET_ID');
        $this->assertEquals('WIDGET_ID', $resource->getId());
    }
}
