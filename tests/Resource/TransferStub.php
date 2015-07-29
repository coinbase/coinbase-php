<?php

namespace Coinbase\Wallet\Tests\Resource;

use Coinbase\Wallet\Resource\Transfer;

class TransferStub extends Transfer
{
    public function __construct($resourcePath = null)
    {
        parent::__construct('test', $resourcePath);
    }
}
