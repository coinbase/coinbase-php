<?php

namespace Coinbase\Wallet\Enum;

/**
 * Supported payment method types.
 */
class PaymentMethodType
{
    const ACH_BANK_ACCOUNT = 'ach_bank_account';
    const BANK_WIRE = 'bank_wire';
    const CREDIT_CARD = 'credit_card';
    const FIAT_ACCOUNT = 'fiat_account';
    const IDEAL_BANK_ACCOUNT = 'ideal_bank_account';
    const SEPA_BANK_ACCOUNT = 'sepa_bank_account';

    private function __construct()
    {
    }
}
