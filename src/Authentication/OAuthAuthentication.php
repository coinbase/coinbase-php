<?php

namespace Coinbase\Wallet\Authentication;

use Coinbase\Wallet\Exception\LogicException;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OAuthAuthentication implements Authentication
{
    private $accessToken;
    private $refreshToken;

    public function __construct($accessToken, $refreshToken = null)
    {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
    }

    public function getRequestHeaders($method, $path, $body)
    {
        return [
            'Authorization' => 'Bearer '.$this->accessToken,
        ];
    }

    public function createRefreshRequest($baseUrl)
    {
        if (!$this->refreshToken) {
            throw new LogicException('There is no refresh token');
        }

        return new Request('POST', $baseUrl.'/oauth/token', [
            'Content-Type' => 'application/json',
        ], json_encode([
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->refreshToken,
        ]));
    }

    public function handleRefreshResponse(RequestInterface $request, ResponseInterface $response)
    {
        $data = json_decode($response->getBody(), true);

        $this->accessToken = $data['access_token'];
        $this->refreshToken = $data['refresh_token'];
    }

    public function createRevokeRequest($baseUrl)
    {
        return new Request('POST', $baseUrl.'/oauth/revoke', [
            'Content-Type' => 'application/json',
        ], json_encode([
            'token' => $this->accessToken,
        ]));
    }

    public function handleRevokeResponse(RequestInterface $request, ResponseInterface $response)
    {
    }
}
