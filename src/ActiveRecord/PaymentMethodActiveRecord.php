<?php

namespace Coinbase\Wallet\ActiveRecord;

trait PaymentMethodActiveRecord
{
    use BaseActiveRecord;

    /**
     * Issues a refresh request to the API.
     */
    public function refresh(array $params = [])
    {
        $this->getClient()->refreshPaymentMethod($this, $params);
    }
}
