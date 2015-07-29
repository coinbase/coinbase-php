<?php

namespace Coinbase\Wallet\Enum;

/**
 * Supported checkout styles.
 */
class CheckoutStyle
{
    const BUY_NOW_LARGE = 'buy_now_large';
    const BUY_NOW_SMALL = 'buy_now_small';
    const CUSTOM_LARGE = 'custom_large';
    const CUSTOM_SMALL = 'custom_small';
    const DONATION_LARGE = 'donation_large';
    const DONATION_SMALL = 'donation_small';
    const NONE = 'none';

    private function __construct()
    {
    }
}
