<?php

namespace Coinbase\Wallet\ActiveRecord;

trait MerchantActiveRecord
{
    use BaseActiveRecord;

    /**
     * Issues a refresh request to the API.
     */
    public function refresh(array $params = [])
    {
        $this->getClient()->refreshMerchant($this, $params);
    }
}
