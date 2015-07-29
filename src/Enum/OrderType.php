<?php

namespace Coinbase\Wallet\Enum;

/**
 * Supported order types.
 */
class OrderType
{
    const DONATION = 'donation';
    const INVOICE = 'invoice';
    const ORDER = 'order';

    private function __construct()
    {
    }
}
