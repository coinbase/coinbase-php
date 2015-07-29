<?php

namespace Coinbase\Wallet\ActiveRecord;

trait CurrentUserActiveRecord 
{
    use BaseActiveRecord;

    /**
     * Issues an API request to update the current user.
     */
    public function update(array $params = [])
    {
        $this->getClient()->updateCurrentUser($this, $params);
    }
}
