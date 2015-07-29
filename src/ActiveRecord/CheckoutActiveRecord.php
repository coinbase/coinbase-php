<?php

namespace Coinbase\Wallet\ActiveRecord;

use Coinbase\Wallet\Resource\Order;
use Coinbase\Wallet\Resource\ResourceCollection;

trait CheckoutActiveRecord
{
    use BaseActiveRecord;

    /**
     * Issues a refresh request to the API.
     */
    public function refresh(array $params = [])
    {
        $this->getClient()->refreshCheckout($this, $params);
    }

    /**
     * Fetches checkout orders from the API.
     *
     * @return ResourceCollection|Order[]
     */
    public function getOrders(array $params = [])
    {
        return $this->getClient()->getCheckoutOrders($this, $params);
    }

    /**
     * Issues an API request to create a new checkout order.
     *
     * @return Order The new order
     */
    public function createNewOrder(array $params = [])
    {
        return $this->getClient()->createNewCheckoutOrder($this, $params);
    }
}
