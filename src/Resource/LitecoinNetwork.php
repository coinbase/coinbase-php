<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\Enum\ResourceType;

class LitecoinNetwork extends Resource
{
    public function __construct()
    {
        parent::__construct(ResourceType::LITECOIN_NETWORK);
    }
}