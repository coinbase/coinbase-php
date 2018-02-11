<?php
/**
 * @author Floran Pagliai
 * Date: 20/12/2017
 * Time: 11:30
 */

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\Enum\ResourceType;
use Coinbase\Wallet\Resource\Resource;

class BitcoinCashNetwork extends Resource
{
    public function __construct()
    {
        parent::__construct(ResourceType::BITCOIN_CASH_NETWORK);
    }
}