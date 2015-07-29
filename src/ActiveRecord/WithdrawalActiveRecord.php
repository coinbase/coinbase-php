<?php

namespace Coinbase\Wallet\ActiveRecord;

trait WithdrawalActiveRecord 
{
    use BaseActiveRecord;

    /**
     * Issues a refresh request to the API.
     */
    public function refresh(array $params = [])
    {
        $this->getClient()->refreshWithdrawal($this, $params);
    }

    /**
     * Issues an API request to commit the current withdrawal.
     */
    public function commit(array $params = [])
    {
        $this->getClient()->commitWithdrawal($this, $params);
    }
}
