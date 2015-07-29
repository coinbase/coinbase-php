<?php

namespace Coinbase\Wallet\Authentication;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface Authentication
{
    /**
     * Returns authentication headers for the given request.
     *
     * @param string $method The request method
     * @param string $path   The request resource path
     * @param string $body   The request body
     *
     * @return array A hash of request headers for authentication
     */
    public function getRequestHeaders($method, $path, $body);

    /** @return RequestInterface|null */
    public function createRefreshRequest($baseUrl);
    public function handleRefreshResponse(RequestInterface $request, ResponseInterface $response);

    /** @return RequestInterface|null */
    public function createRevokeRequest($baseUrl);
    public function handleRevokeResponse(RequestInterface $request, ResponseInterface $response);
}
