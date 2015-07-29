<?php

namespace Coinbase\Wallet\ActiveRecord;

trait BaseActiveRecord
{
    private function getClient()
    {
        return ActiveRecordContext::getClient();
    }
}
