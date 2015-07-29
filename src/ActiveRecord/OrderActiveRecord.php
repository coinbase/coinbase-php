<?php

namespace Coinbase\Wallet\ActiveRecord;

trait OrderActiveRecord
{
    use BaseActiveRecord;

    /**
     * Issues a refresh request to the API.
     */
    public function refresh(array $params = [])
    {
        $this->getClient()->refreshOrder($this, $params);
    }

    /**
     * Issues an API request to refund the current order.
     */
    public function refund(array $params = [])
    {
        $this->getClient()->refundOrder($this, $params);
    }
}
