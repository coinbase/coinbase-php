<?php

namespace Coinbase\Wallet\Tests;

use Coinbase\Wallet\Authentication\Authentication;
use Coinbase\Wallet\Configuration;
use Coinbase\Wallet\Enum\Param;
use Coinbase\Wallet\HttpClient;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClientTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|ClientInterface */
    private $transport;

    /** @var HttpClient */
    private $client;

    public static function setUpBeforeClass()
    {
        date_default_timezone_set('America/New_York');
    }

    protected function setUp()
    {
        $this->transport = $this->getMock(ClientInterface::class);
        $this->client = $this->createHttpClient(new AuthenticationStub());
    }

    protected function tearDown()
    {
        $this->transport = null;
        $this->client = null;
    }

    /**
     * @dataProvider provideForGetQueryString
     */
    public function testGetQueryString($path, $expected)
    {
        $this->transport->expects($this->once())
            ->method('send')
            ->with($this->isRequestFor('GET', $expected));

        $this->client->get($path, ['foo' => 'bar']);
    }

    public function provideForGetQueryString()
    {
        return [
            ['/', '/?foo=bar'],
            ['/?bar=foo', '/?bar=foo&foo=bar'],
        ];
    }

    /**
     * @dataProvider provideForUnsafeMethod
     */
    public function testUnsafeMethod($method, $httpMethod)
    {
        $this->transport->expects($this->once())
            ->method('send')
            ->with($this->isRequestFor($httpMethod, '/'));

        $this->client->$method('/', ['foo' => 'bar']);
    }

    public function provideForUnsafeMethod()
    {
        return [
            ['put', 'PUT'],
            ['post', 'POST'],
            ['delete', 'DELETE'],
        ];
    }

    public function testOptions()
    {
        $this->transport->expects($this->once())
            ->method('send')
            ->with(
                $this->isRequestFor('POST', '/'),
                $this->isValidOptionsArray(['auth'])
            );

        $this->client->post('/', ['foo' => 'bar']);
    }

    public function testAuthenticationHeaders()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Authentication $auth */
        $auth = $this->getMock(Authentication::class);

        $auth->expects($this->once())
            ->method('getRequestHeaders')
            ->with('POST', '/', '{"foo":"bar"}')
            ->willReturn([]);

        $client = $this->createHttpClient($auth);
        $client->post('/', ['foo' => 'bar']);
    }

    public function testRefreshAuthentication()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Authentication $auth */
        $auth = $this->getMock(Authentication::class);
        $request = $this->getMock(RequestInterface::class);
        $response = $this->getMock(ResponseInterface::class);

        $auth->expects($this->any())
            ->method('getRequestHeaders')
            ->willReturn(['auth' => 'auth']);
        $auth->expects($this->any())
            ->method('createRefreshRequest')
            ->with(Configuration::DEFAULT_API_URL)
            ->willReturn($request);
        $this->transport->expects($this->once())
            ->method('send')
            ->with($request, $this->isValidOptionsArray(['auth']))
            ->willReturn($response);
        $auth->expects($this->once())
            ->method('handleRefreshResponse')
            ->with($request, $response);

        $client = $this->createHttpClient($auth);
        $client->refreshAuthentication(['foo' => 'bar']);
    }

    public function testRefreshAuthenticationNoRequest()
    {
        $this->transport->expects($this->never())->method('send');
        $this->client->refreshAuthentication(['foo' => 'bar']);
    }

    public function testRevokeAuthentication()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Authentication $auth */
        $auth = $this->getMock(Authentication::class);
        $request = $this->getMock(RequestInterface::class);
        $response = $this->getMock(ResponseInterface::class);

        $auth->expects($this->any())
            ->method('getRequestHeaders')
            ->willReturn(['auth' => 'auth']);
        $auth->expects($this->any())
            ->method('createRevokeRequest')
            ->with(Configuration::DEFAULT_API_URL)
            ->willReturn($request);
        $this->transport->expects($this->once())
            ->method('send')
            ->with($request, $this->isValidOptionsArray(['auth']))
            ->willReturn($response);
        $auth->expects($this->once())
            ->method('handleRevokeResponse')
            ->with($request, $response);

        $client = $this->createHttpClient($auth);
        $client->revokeAuthentication(['foo' => 'bar']);
    }

    public function testRevokeAuthenticationNoRequest()
    {
        $this->transport->expects($this->never())->method('send');
        $this->client->revokeAuthentication(['foo' => 'bar']);
    }

    public function testTwoFactorTokenGet()
    {
        $this->transport->expects($this->once())
            ->method('send')
            ->with(
                $this->isRequestFor('GET', '/?foo=bar'),
                $this->isValidOptionsArray(['CB-2FA-TOKEN'], false)
            );

        $this->client->get('/', [
            'foo' => 'bar',
            Param::TWO_FACTOR_TOKEN => 'TOKEN',
        ]);
    }

    public function testTwoFactorTokenPost()
    {
        $this->transport->expects($this->once())
            ->method('send')
            ->with(
                $this->isRequestFor('POST', '/'),
                $this->isValidOptionsArray(['CB-2FA-TOKEN'])
            );

        $this->client->post('/', [
            'foo' => 'bar',
            Param::TWO_FACTOR_TOKEN => 'TOKEN',
        ]);
    }

    // private

    private function createHttpClient(Authentication $auth)
    {
        return new HttpClient(
            Configuration::DEFAULT_API_URL,
            Configuration::DEFAULT_API_VERSION,
            $auth,
            $this->transport
        );
    }

    private function isValidOptionsArray(array $headers = [], $json = true)
    {
        return $this->callback(function($options) use($headers, $json) {
            $this->assertArrayHasKey('headers', $options);
            $this->assertArrayHasKey('User-Agent', $options['headers']);
            $this->assertArrayHasKey('CB-VERSION', $options['headers']);

            if ($json) {
                $this->assertArrayHasKey('json', $options);
            }

            foreach ($headers as $header) {
                $this->assertArrayHasKey($header, $options['headers']);
            }

            return true;
        });
    }

    private function isRequestFor($method, $path)
    {
        return $this->callback(
            function($request) use($method, $path) {
                /** @var RequestInterface $request */
                $this->assertInstanceOf(RequestInterface::class, $request);
                $this->assertEquals($method, $request->getMethod());
                $this->assertEquals($path, $request->getRequestTarget());

                return true;
            }
        );
    }
}
