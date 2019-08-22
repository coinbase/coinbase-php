<?php

namespace Coinbase\Wallet\Resource;
use Coinbase\Wallet\Enum\ResourceType;

class USDCoinAddress extends Resource
{
    private $address;

    public function __construct($address)
    {
        parent::__construct(ResourceType::USD_COIN_ADDRESS);

        $this->address = $address;
    }

    public function getAddress()
    {
        return $this->address;
    }
}