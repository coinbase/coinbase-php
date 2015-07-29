<?php

namespace Coinbase\Wallet\Resource;

use Coinbase\Wallet\ActiveRecord\BuyActiveRecord;
use Coinbase\Wallet\Enum\ResourceType;
use Coinbase\Wallet\Value\Money;

class Buy extends Transfer
{
    use BuyActiveRecord;

    /** @var Money */
    private $total;

    /** @var Boolean */
    private $instant;

    /**
     * Creates a buy reference.
     *
     * @param string $accountId The account id
     * @param string $buyId     The buy id
     *
     * @return Buy A buy reference
     */
    public static function reference($accountId, $buyId)
    {
        $resourcePath = sprintf('/v2/accounts/%s/buys/%s', $accountId, $buyId);

        return new static($resourcePath);
    }

    public function __construct($resourcePath = null)
    {
        parent::__construct(ResourceType::BUY, $resourcePath);
    }

    public function getTotal()
    {
        return $this->total;
    }

    public function setTotal(Money $total)
    {
        $this->total = $total;
    }

    public function isInstant()
    {
        return $this->instant;
    }
}
