<?php

namespace Coinbase\Wallet\ActiveRecord;

trait DepositActiveRecord 
{
    use BaseActiveRecord;

    /**
     * Issues a refresh request to the API.
     */
    public function refresh(array $params = [])
    {
        $this->getClient()->refreshDeposit($this, $params);
    }

    /**
     * Issues an API request to commit the current deposit.
     */
    public function commit(array $params = [])
    {
        $this->getClient()->commitDeposit($this, $params);
    }
}
