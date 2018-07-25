<?php
/**
 * Created by: KAMAELUA <kamaelua@gmail.com>
 */

namespace Coinbase\Wallet\Resource;


use Coinbase\Wallet\Enum\ResourceType;

class BitcoinCashAddress extends Resource
{
    private $address;

    public function __construct($address)
    {
        parent::__construct(ResourceType::BITCOIN_CASH_ADDRESS);

        $this->address = $address;
    }

    public function getAddress()
    {
        return $this->address;
    }
}