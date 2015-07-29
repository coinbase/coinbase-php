<?php

namespace Coinbase\Wallet\ActiveRecord;

trait SellActiveRecord 
{
    use BaseActiveRecord;

    /**
     * Issues a refresh request to the API.
     */
    public function refresh(array $params = [])
    {
        $this->getClient()->refreshSell($this, $params);
    }

    /**
     * Issues an API request to commit the current sell.
     */
    public function commit(array $params = [])
    {
        $this->getClient()->commitSell($this, $params);
    }
}
