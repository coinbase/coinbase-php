<?php

namespace Coinbase\Wallet\Enum;

/**
 * Supported transaction types.
 */
class TransactionType
{
    const BUY = 'buy';
    const EXCHANGE_DEPOSIT = 'exchange_deposit';
    const EXCHANGE_WITHDRAWAL = 'exchange_withdrawal';
    const FIAT_DEPOSIT = 'fiat_deposit';
    const REQUEST = 'request';
    const SELL = 'sell';
    const SEND = 'send';
    const TRANSFER = 'transfer';
    const VAULT_WITHDRAWAL = 'vault_withdrawal';

    private function __construct()
    {
    }
}
