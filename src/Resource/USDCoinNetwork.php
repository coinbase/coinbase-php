<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\Enum\ResourceType;

class USDCoinNetwork extends Resource
{
    public function __construct()
    {
        parent::__construct(ResourceType::USD_COIN_NETWORK);
    }
}