<?php
/**
 * @author Floran Pagliai
 * Date: 22/12/2017
 * Time: 19:09
 */

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\Enum\ResourceType;

class EthereumNetwork extends Resource
{
    public function __construct()
    {
        parent::__construct(ResourceType::LITECOIN_NETWORK);
    }
}