<?php

namespace Coinbase\Wallet\Enum;

/**
 * Supported notification types
 */
class NotificationType
{
    const PING = 'ping';

    const ADDRESS_NEW_PAYMENT = 'wallet:addresses:new-payment';

    const BUY_CREATED = 'wallet:buys:created';
    const BUY_COMPLETED = 'wallet:buys:completed';
    const BUY_CANCELED = 'wallet:buys:canceled';

    const SELL_CREATED = 'wallet:sells:created';
    const SELL_COMPLETED = 'wallet:sells:completed';
    const SELL_CANCELED = 'wallet:sells:canceled';

    const DEPOSIT_CREATED = 'wallet:deposit:created';
    const DEPOSIT_COMPLETED = 'wallet:deposit:completed';
    const DEPOSIT_CANCELED = 'wallet:deposit:canceled';

    const WITHDRAWAL_CREATED = 'wallet:withdrawal:created';
    const WITHDRAWAL_COMPLETED = 'wallet:withdrawal:completed';
    const WITHDRAWAL_CANCELED = 'wallet:withdrawal:canceled';

    const ORDER_PAID = 'wallet:orders:paid';
    const ORDER_MISPAID = 'wallet:orders:mispaid';

    const MERCHANT_PAYOUT_CREATED = 'wallet:merchant-payouts:created';

    const WALLET = 'wallet';

    private function __construct()
    {
    }
}
