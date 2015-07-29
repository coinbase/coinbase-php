<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\ActiveRecord\WithdrawalActiveRecord;
use Coinbase\Wallet\Enum\ResourceType;

class Withdrawal extends Transfer
{
    use WithdrawalActiveRecord;

    /**
     * Creates a withdrawal reference.
     *
     * @param string $accountId    The account id
     * @param string $withdrawalId The withdrawal id
     *
     * @return Withdrawal A withdrawal reference
     */
    public static function reference($accountId, $withdrawalId)
    {
        $resourcePath = sprintf('/v2/accounts/%s/withdrawals/%s', $accountId, $withdrawalId);

        return new static($resourcePath);
    }

    public function __construct($resourcePath = null)
    {
        parent::__construct(ResourceType::WITHDRAWAL, $resourcePath);
    }
}
