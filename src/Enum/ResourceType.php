<?php

namespace Coinbase\Wallet\Enum;

/**
 * Supported resource types.
 */
class ResourceType
{
    const ACCOUNT = 'account';
    const ADDRESS = 'address';
    const APPLICATION = 'application';
    const BITCOIN_ADDRESS = 'bitcoin_address';
    const BUY = 'buy';
    const CHECKOUT = 'checkout';
    const DEPOSIT = 'deposit';
    const EMAIL = 'email';
    const MERCHANT = 'merchant';
    const ORDER = 'order';
    const PAYMENT_METHOD = 'payment_method';
    const SELL = 'sell';
    const TRANSACTION = 'transaction';
    const USER = 'user';
    const WITHDRAWAL = 'withdrawal';
    const BITCOIN_NETWORK = 'bitcoin_network';
    const NOTIFICATION = 'notification';

    private function __construct()
    {
    }
}
