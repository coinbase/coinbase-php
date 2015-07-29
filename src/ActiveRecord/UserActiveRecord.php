<?php

namespace Coinbase\Wallet\ActiveRecord;

trait UserActiveRecord
{
    use BaseActiveRecord;

    /**
     * Issues a refresh request to the API.
     */
    public function refresh(array $params = [])
    {
        $this->getClient()->refreshUser($this, $params);
    }
}
