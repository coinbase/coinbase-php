<?php

namespace Coinbase\Wallet\Enum;

/**
 * Supported parameter keys.
 */
class Param
{
    const AGREE_BTC_AMOUNT_VARIES = 'agree_btc_amount_varies';
    const COMMIT = 'commit';
    const ENDING_BEFORE = 'ending_before';
    const EXPAND = 'expand';
    const FEE = 'fee';
    const FETCH_ALL = 'fetch_all';
    const IDEM = 'idem';
    const LIMIT = 'limit';
    const MISPAYMENT = 'mispayment';
    const ORDER = 'order';
    const QUOTE = 'quote';
    const REFUND_ADDRESS = 'refund_address';
    const SKIP_NOTIFICATIONS = 'skip_notifications';
    const STARTING_AFTER = 'starting_after';
    const TWO_FACTOR_TOKEN = 'two_factor_token';

    private function __construct()
    {
    }
}
