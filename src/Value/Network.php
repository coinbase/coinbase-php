<?php

namespace Coinbase\Wallet\Value;

class Network
{
    /** @var string */
    private $status;

    /** @var string */
    private $hash;

    public function __construct($status, $hash)
    {
        $this->status = $status;
        $this->hash = $hash;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getHash()
    {
        return $this->hash;
    }
}
