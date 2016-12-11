<?php

namespace Coinbase\Wallet;

use Coinbase\Wallet\Authentication\ApiKeyAuthentication;
use Coinbase\Wallet\Authentication\Authentication;
use Coinbase\Wallet\Authentication\OAuthAuthentication;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

class Configuration
{
    const DEFAULT_API_URL = 'https://api.coinbase.com';
    const DEFAULT_API_VERSION = '2016-02-01';

    private $authentication;
    private $apiUrl;
    private $apiVersion;
    private $caBundle;
    private $logger;

    /**
     * Creates a new configuration with OAuth authentication.
     *
     * @param string $accessToken  An OAuth access token
     * @param string $refreshToken An OAuth refresh token
     *
     * @return Configuration A new configuration instance
     */
    public static function oauth($accessToken, $refreshToken = null)
    {
        return new static(
            new OAuthAuthentication($accessToken, $refreshToken)
        );
    }

    /**
     * Creates a new configuration with API key authentication.
     *
     * @param string $apiKey    An API key
     * @param string $apiSecret An API secret
     *
     * @return Configuration A new configuration instance
     */
    public static function apiKey($apiKey, $apiSecret)
    {
        return new static(
            new ApiKeyAuthentication($apiKey, $apiSecret)
        );
    }

    public function __construct(Authentication $authentication)
    {
        $this->authentication = $authentication;
        $this->apiUrl = self::DEFAULT_API_URL;
        $this->apiVersion = self::DEFAULT_API_VERSION;
        $this->caBundle = __DIR__.'/../etc/ca-coinbase.crt';
    }

    /** @return HttpClient */
    public function createHttpClient(ClientInterface $transport = null)
    {
        $httpClient = new HttpClient(
            $this->apiUrl,
            $this->apiVersion,
            $this->authentication,
            $transport ?: new GuzzleClient()
        );

        $httpClient->setCaBundle($this->caBundle);
        $httpClient->setLogger($this->logger);

        return $httpClient;
    }

    /** @return Mapper */
    public function createMapper()
    {
        return new Mapper();
    }

    public function getAuthentication()
    {
        return $this->authentication;
    }

    public function setAuthentication(Authentication $authentication)
    {
        $this->authentication = $authentication;
    }

    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    }

    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
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
}
