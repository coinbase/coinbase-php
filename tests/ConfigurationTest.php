<?php

namespace Coinbase\Wallet\Tests;

use Coinbase\Wallet\Configuration;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultApiUrl()
    {
        $config = new Configuration(new AuthenticationStub());
        $this->assertEquals(Configuration::DEFAULT_API_URL, $config->getApiUrl());
    }

    public function testCustomApiUrl()
    {
        $config = new Configuration(new AuthenticationStub());
        $config->setApiUrl('https://example.com');
        $this->assertEquals('https://example.com', $config->getApiUrl());
    }

    public function testDefaultApiVersion()
    {
        $config = new Configuration(new AuthenticationStub());
        $this->assertEquals(Configuration::DEFAULT_API_VERSION, $config->getApiVersion());
    }

    public function testCustomApiVersion()
    {
        $config = new Configuration(new AuthenticationStub());
        $config->setApiVersion('2000-01-01');
        $this->assertEquals('2000-01-01', $config->getApiVersion());
    }
}
