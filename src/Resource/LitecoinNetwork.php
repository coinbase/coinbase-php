<?php
/**
 * @author Floran Pagliai
 * Date: 21/12/2017
 * Time: 09:01
 */

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\Enum\ResourceType;
use Coinbase\Wallet\Resource\Resource;

class LitecoinNetwork extends Resource
{
    public function __construct()
    {
        parent::__construct(ResourceType::LITECOIN_NETWORK);
    }
}