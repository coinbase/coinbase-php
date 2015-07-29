<?php

namespace Coinbase\Wallet\ActiveRecord;

use Coinbase\Wallet\Client;
use Coinbase\Wallet\Exception\LogicException;

class ActiveRecordContext
{
    /** @var Client */
    private static $client;

    public static function getClient()
    {
        if (!self::$client) {
            throw new LogicException('You must call enableActiveRecord() on your client before calling this method');
        }

        return self::$client;
    }

    public static function setClient(Client $client = null)
    {
        self::$client = $client;
    }

    private function __construct()
    {
    }
}
