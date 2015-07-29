<?php

namespace Coinbase\Wallet\Tests\ActiveRecord;

use Coinbase\Wallet\ActiveRecord\ActiveRecordContext;
use Coinbase\Wallet\Client;
use Coinbase\Wallet\Resource\Deposit;

class DepositActiveRecordTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client */
    private $client;

    /** @var Deposit */
    private $deposit;

    protected function setUp()
    {
        $this->client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        ActiveRecordContext::setClient($this->client);

        $this->deposit = new Deposit();
    }

    protected function tearDown()
    {
        $this->client = null;
        $this->deposit = null;
    }

    /**
     * @dataProvider provideForMethodProxy
     */
    public function testMethodProxy($method, $clientMethod)
    {
        $this->client->expects($this->once())
            ->method($clientMethod)
            ->with($this->deposit, []);

        $this->deposit->$method();
    }

    public function provideForMethodProxy()
    {
        return [
            'refresh' => ['refresh', 'refreshDeposit'],
            'commit'  => ['commit', 'commitDeposit'],
        ];
    }
}
