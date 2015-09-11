<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\Enum\ResourceType;

class BitcoinNetwork extends Resource
{
    public function __construct()
    {
        parent::__construct(ResourceType::BITCOIN_NETWORK);
    }
}
