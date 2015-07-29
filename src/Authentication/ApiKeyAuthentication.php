<?php

namespace Coinbase\Wallet\Authentication;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ApiKeyAuthentication implements Authentication
{
    private $apiKey;
    private $apiSecret;

    public function __construct($apiKey, $apiSecret)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getApiSecret()
    {
        return $this->apiSecret;
    }

    public function setApiSecret($apiSecret)
    {
        $this->apiSecret = $apiSecret;
    }

    public function getRequestHeaders($method, $path, $body)
    {
        $timestamp = $this->getTimestamp();
        $signature = $this->getHash('sha256', $timestamp.$method.$path.$body, $this->apiSecret);

        return [
            'CB-ACCESS-KEY'       => $this->apiKey,
            'CB-ACCESS-SIGN'      => $signature,
            'CB-ACCESS-TIMESTAMP' => $timestamp,
        ];
    }

    public function createRefreshRequest($baseUrl)
    {
    }

    public function handleRefreshResponse(RequestInterface $request, ResponseInterface $response)
    {
    }

    public function createRevokeRequest($baseUrl)
    {
    }

    public function handleRevokeResponse(RequestInterface $request, ResponseInterface $response)
    {
    }

    // protected

    protected function getTimestamp()
    {
        return time();
    }

    protected function getHash($algo, $data, $key)
    {
        return hash_hmac($algo, $data, $key);
    }
}
