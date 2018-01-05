<?php

namespace Coinbase\Wallet\Value;

class Network
{
    /** @var string */
    private $status;

    /** @var string */
    private $hash;

    /** @var Money */
    private $transactionFee;

    public function __construct($status, $hash, $transactionFee)
    {
        $this->status = $status;
        $this->hash = $hash;
        $this->transactionFee = $transactionFee ? new Money($transactionFee['amount'], $transactionFee['currency']) : null;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getHash()
    {
        return $this->hash;
    }

    public function getTransactionFee()
    {
        return $this->transactionFee;
    }

}
