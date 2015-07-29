<?php

namespace Coinbase\Wallet\Tests\Authentication;

use Coinbase\Wallet\Authentication\OAuthAuthentication;
use Coinbase\Wallet\Configuration;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class OAuthAuthenticationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetRequestHeaders()
    {
        $expected = [
            'Authorization' => 'Bearer ACCESS_TOKEN',
        ];

        $auth = new OAuthAuthentication('ACCESS_TOKEN');
        $actual = $auth->getRequestHeaders('POST', '/', '{"foo":"bar"}');
        $this->assertEquals($expected, $actual);
    }

    public function testCreateRefreshRequest()
    {
        $expected = [
            'grant_type' => 'refresh_token',
            'refresh_token' => 'REFRESH_TOKEN',
        ];

        $auth = new OAuthAuthentication('ACCESS_TOKEN', 'REFRESH_TOKEN');
        $request = $auth->createRefreshRequest(Configuration::DEFAULT_API_URL);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/oauth/token', $request->getRequestTarget());
        $this->assertEquals($expected, json_decode($request->getBody(), true));
    }

    /**
     * @expectedException \Coinbase\Wallet\Exception\LogicException
     */
    public function testCreateRefreshRequestNoToken()
    {
        $auth = new OAuthAuthentication('ACCESS_TOKEN');
        $auth->createRefreshRequest(Configuration::DEFAULT_API_URL);
    }

    public function testHandleRefreshResponse()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|RequestInterface $response */
        $request = $this->getMock(RequestInterface::class);
        /** @var \PHPUnit_Framework_MockObject_MockObject|ResponseInterface $response */
        $response = $this->getMock(ResponseInterface::class);
        $stream = $this->getMock(StreamInterface::class);

        $response->expects($this->any())
            ->method('getBody')
            ->willReturn($stream);
        $stream->expects($this->any())
            ->method('__toString')
            ->willReturn('{"access_token":"NEW_ACCESS","refresh_token":"NEW_REFRESH"}');

        $auth = new OAuthAuthentication('OLD_ACCESS', 'OLD_REFRESH');
        $auth->handleRefreshResponse($request, $response);
        $this->assertEquals('NEW_ACCESS', $auth->getAccessToken());
        $this->assertEquals('NEW_REFRESH', $auth->getRefreshToken());
    }

    public function testCreateRevokeRequest()
    {
        $expected = [
            'token' => 'ACCESS_TOKEN',
        ];

        $auth = new OAuthAuthentication('ACCESS_TOKEN');
        $request = $auth->createRevokeRequest(Configuration::DEFAULT_API_URL);

        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('/oauth/revoke', $request->getRequestTarget());
        $this->assertEquals($expected, json_decode($request->getBody(), true));
    }
}
