<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\ActiveRecord\DepositActiveRecord;
use Coinbase\Wallet\Enum\ResourceType;

class Deposit extends Transfer
{
    use DepositActiveRecord;

    /**
     * Creates a deposit reference.
     *
     * @param string $accountId The account id
     * @param string $depositId The deposit id
     *
     * @return Deposit A deposit reference
     */
    public static function reference($accountId, $depositId)
    {
        $resourcePath = sprintf('/v2/accounts/%s/deposits/%s', $accountId, $depositId);

        return new static($resourcePath);
    }

    public function __construct($resourcePath = null)
    {
        parent::__construct(ResourceType::DEPOSIT, $resourcePath);
    }
}
