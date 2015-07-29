<?php

namespace Coinbase\Wallet\Value;

class Fee
{
    /** @var string */
    private $type;

    /** @var Money */
    private $amount;

    public function __construct($type, Money $amount)
    {
        $this->type = $type;
        $this->amount = $amount;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getAmount()
    {
        return $this->amount;
    }
}
