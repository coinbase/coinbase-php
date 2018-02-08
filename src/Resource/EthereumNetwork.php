<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\Enum\ResourceType;

class EthereumNetwork extends Resource
{
    public function __construct()
    {
        parent::__construct(ResourceType::ETHEREUM_NETWORK);
    }
}