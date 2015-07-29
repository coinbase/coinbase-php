<?php

namespace Coinbase\Wallet\Enum;

/**
 * Supported order statuses.
 */
class OrderStatus
{
    const ACTIVE = 'active';
    const EXPIRED = 'expired';
    const MISPAID = 'mispaid';
    const PAID = 'paid';
    const PENDING = 'pending';

    private function __construct()
    {
    }
}
