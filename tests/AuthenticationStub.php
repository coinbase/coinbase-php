<?php

namespace Coinbase\Wallet\Tests;

use Coinbase\Wallet\Authentication\Authentication;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AuthenticationStub implements Authentication
{
    public function getRequestHeaders($method, $path, $body)
    {
        return ['auth' => 'auth'];
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
}
