<?php

namespace Coinbase\Wallet\Enum;

/**
 * Supported error codes.
 */
class ErrorCode
{
    const AUTHENTICATION_ERROR = 'authentication_error';
    const EXPIRED_TOKEN = 'expired_token';
    const INTERNAL_SERVER_ERROR = 'internal_server_error';
    const INVALID_REQUEST = 'invalid_request';
    const INVALID_SCOPE = 'invalid_scope';
    const INVALID_TOKEN = 'invalid_token';
    const NOT_FOUND = 'not_found';
    const PARAM_REQUIRED = 'param_required';
    const PERSONAL_DETAILS_REQUIRED = 'personal_details_required';
    const RATE_LIMIT_EXCEEDED = 'rate_limit_exceeded';
    const REVOKED_TOKEN = 'revoked_token';
    const TWO_FACTOR_REQUIRED = 'two_factor_required';
    const UNVERIFIED_EMAIL = 'unverified_email';
    const VALIDATION_ERROR = 'validation_error';

    private function __construct()
    {
    }
}
