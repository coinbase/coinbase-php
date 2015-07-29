<?php

namespace Coinbase\Wallet\Enum;

/**
 * Supported account types.
 */
class AccountType
{
    const FIAT = 'fiat';
    const MULTISIG = 'multisig';
    const MULTISIG_VAULT = 'multisig_vault';
    const VAULT = 'vault';
    const WALLET = 'wallet';

    private function __construct()
    {
    }
}
