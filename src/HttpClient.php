<?php

namespace Coinbase\Wallet;

use Coinbase\Wallet\Authentication\Authentication;
use Coinbase\Wallet\Enum\Param;
use Coinbase\Wallet\Exception\HttpException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class HttpClient
{
    private $apiUrl;
    private $apiVersion;
    private $auth;
    private $transport;
    private $caBundle;

    /** @var LoggerInterface */
    private $logger;

    /** @var RequestInterface */
    private $lastRequest;

    /** @var ResponseInterface */
    private $lastResponse;

    public function __construct($apiUrl, $apiVersion, Authentication $auth, ClientInterface $transport)
    {
        $this->apiUrl = rtrim($apiUrl, '/');
        $this->apiVersion = $apiVersion;
        $this->auth = $auth;
        $this->transport = $transport;
    }

    public function getCaBundle()
    {
        return $this->caBundle;
    }

    public function setCaBundle($caBundle)
    {
        $this->caBundle = $caBundle;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function setLogger(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /** @return ResponseInterface */
    public function get($path, array $params = [])
    {
        return $this->request('GET', $path, $params);
    }

    /** @return ResponseInterface */
    public function put($path, array $params = [])
    {
        return $this->request('PUT', $path, $params);
    }

    /** @return ResponseInterface */
    public function post($path, array $params = [])
    {
        return $this->request('POST', $path, $params);
    }

    /** @return ResponseInterface */
    public function delete($path, array $params = [])
    {
        return $this->request('DELETE', $path, $params);
    }

    public function refreshAuthentication(array $params = [])
    {
        if ($request = $this->auth->createRefreshRequest($this->apiUrl)) {
            $response = $this->send($request, $params);
            $this->auth->handleRefreshResponse($request, $response);
        }
    }

    public function revokeAuthentication(array $params = [])
    {
        if ($request = $this->auth->createRevokeRequest($this->apiUrl)) {
            $response = $this->send($request, $params);
            $this->auth->handleRevokeResponse($request, $response);
        }
    }

    // private

    private function request($method, $path, array $params = [])
    {
        if ('GET' === $method) {
            $path = $this->prepareQueryString($path, $params);
        }

        $request = new Request($method, $this->prepareUrl($path));

        return $this->send($request, $params);
    }

    private function send(RequestInterface $request, array $params = [])
    {
        $this->lastRequest = $request;

        $options = $this->prepareOptions(
            $request->getMethod(),
            $request->getRequestTarget(),
            $params
        );

        try {
            $this->lastResponse = $response = $this->transport->send($request, $options);
        } catch (RequestException $e) {
            throw HttpException::wrap($e);
        }

        if ($this->logger) {
            $this->logWarnings($response);
        }

        return $response;
    }

    private function prepareQueryString($path, array &$params = [])
    {
        if (!$params) {
            return $path;
        }

        // omit two_factor_token
        $query = array_diff_key($params, [Param::TWO_FACTOR_TOKEN => true]);
        $params = array_intersect_key($params, [Param::TWO_FACTOR_TOKEN => true]);

        $path .= false === strpos($path, '?') ? '?' : '&';
        $path .= http_build_query($query, '', '&');

        return $path;
    }

    private function prepareUrl($path)
    {
        return $this->apiUrl.'/'.ltrim($path, '/');
    }

    private function prepareOptions($method, $path, array $params = [])
    {
        $options = [];

        if ($this->caBundle) {
            $options[RequestOptions::VERIFY] = $this->caBundle;
        }

        // omit two_factor_token
        $data = array_diff_key($params, [Param::TWO_FACTOR_TOKEN => true]);
        if ($data) {
            $options[RequestOptions::JSON] = $data;
            $body = json_encode($data);
        } else {
            $body = '';
        }

        $defaultHeaders = [
            'User-Agent' => 'coinbase/php/'.Client::VERSION,
            'CB-VERSION' => $this->apiVersion,
            'Content-Type' => 'application/json',
        ];

        if (isset($params[Param::TWO_FACTOR_TOKEN])) {
            $defaultHeaders['CB-2FA-TOKEN'] = $params[Param::TWO_FACTOR_TOKEN];
        }

        $options[RequestOptions::HEADERS] = $defaultHeaders + $this->auth->getRequestHeaders(
            $method,
            $path,
            $body
        );

        return $options;
    }

    private function logWarnings(ResponseInterface $response)
    {
        $body = (string) $response->getBody();
        if (false === strpos($body, '"warnings"')) {
            return;
        }

        $data = json_decode($body, true);
        if (!isset($data['warnings'])) {
            return;
        }

        foreach ($data['warnings'] as $warning) {
            $this->logger->warning(isset($warning['url'])
                ? sprintf('%s (%s)', $warning['message'], $warning['url'])
                : $warning['message']);
        }
    }
}
