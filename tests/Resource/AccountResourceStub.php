<?php

namespace Coinbase\Wallet\Tests\Resource;

use Coinbase\Wallet\Resource\AccountResource;
use Coinbase\Wallet\Resource\Resource;

class AccountResourceStub extends Resource
{
    use AccountResource;

    public function __construct($resourcePath = null)
    {
        parent::__construct('test', $resourcePath);
    }
}
