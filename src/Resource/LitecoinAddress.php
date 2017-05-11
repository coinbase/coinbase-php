<?php

namespace Coinbase\Wallet\Resource;
use Coinbase\Wallet\Enum\ResourceType;

class LitecoinAddress extends Resource
{
    private $address;

    public function __construct($address)
    {
        parent::__construct(ResourceType::LITECOIN_ADDRESS);

        $this->address = $address;
    }

    public function getAddress()
    {
        return $this->address;
    }
}