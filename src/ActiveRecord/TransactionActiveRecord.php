<?php

namespace Coinbase\Wallet\ActiveRecord;

trait TransactionActiveRecord 
{
    use BaseActiveRecord;

    /**
     * Issues a refresh request to the API.
     */
    public function refresh(array $params = [])
    {
        $this->getClient()->refreshTransaction($this, $params);
    }

    /**
     * Issues an API request to complete the current transaction.
     */
    public function complete(array $params = [])
    {
        $this->getClient()->completeTransaction($this, $params);
    }

    /**
     * Issues an API request to resend the current transaction.
     */
    public function resend(array $params = [])
    {
        $this->getClient()->resendTransaction($this, $params);
    }

    /**
     * Issues an API request to cancel the current transaction.
     */
    public function cancel(array $params = [])
    {
        $this->getClient()->cancelTransaction($this, $params);
    }
}
