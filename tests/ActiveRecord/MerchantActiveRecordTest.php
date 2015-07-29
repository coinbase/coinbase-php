<?php

namespace Coinbase\Wallet\Tests\ActiveRecord;

use Coinbase\Wallet\ActiveRecord\ActiveRecordContext;
use Coinbase\Wallet\Client;
use Coinbase\Wallet\Resource\Merchant;

class MerchantActiveRecordTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client */
    private $client;

    /** @var Merchant */
    private $merchant;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        ActiveRecordContext::setClient($this->client);

        $this->merchant = new Merchant();
    }

    protected function tearDown()
    {
        $this->client = null;
        $this->merchant = null;
    }

    /**
     * @dataProvider provideForMethodProxy
     */
    public function testMethodProxy($method, $clientMethod)
    {
        $this->client->expects($this->once())
            ->method($clientMethod)
            ->with($this->merchant, []);

        $this->merchant->$method();
    }

    public function provideForMethodProxy()
    {
        return [
            'refresh' => ['refresh', 'refreshMerchant'],
        ];
    }
}
