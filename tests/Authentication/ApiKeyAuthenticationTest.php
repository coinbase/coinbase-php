<?php

namespace Coinbase\Wallet\Tests\Authentication;

use Coinbase\Wallet\Authentication\ApiKeyAuthentication;

class ApiKeyAuthenticationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRequestHeaders()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ApiKeyAuthentication $auth */
        $auth = $this->getMockBuilder(ApiKeyAuthentication::class)
            ->setConstructorArgs(['KEY', 'SECRET'])
            ->setMethods(['getTimestamp', 'getHash'])
            ->getMock();

        $auth->expects($this->any())
            ->method('getTimestamp')
            ->willReturn(123);
        $auth->expects($this->once())
            ->method('getHash')
            ->with('sha256', '123POST/{"foo":"bar"}', 'SECRET')
            ->willReturn('HASH');

        $expected = [
            'CB-ACCESS-KEY' => 'KEY',
            'CB-ACCESS-SIGN' => 'HASH',
            'CB-ACCESS-TIMESTAMP' => 123,
        ];

        $actual = $auth->getRequestHeaders('POST', '/', '{"foo":"bar"}');
        $this->assertEquals($expected, $actual);
    }
}
