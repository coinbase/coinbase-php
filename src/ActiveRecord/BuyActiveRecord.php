<?php

namespace Coinbase\Wallet\ActiveRecord;

trait BuyActiveRecord 
{
    use BaseActiveRecord;

    /**
     * Issues a refresh request to the API.
     */
    public function refresh(array $params = [])
    {
        $this->getClient()->refreshBuy($this, $params);
    }

    /**
     * Issues an API request to commit the current buy.
     */
    public function commit(array $params = [])
    {
        $this->getClient()->commitBuy($this, $params);
    }
}
