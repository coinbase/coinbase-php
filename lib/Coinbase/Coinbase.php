<?php

class Coinbase
{
    const API_BASE = 'https://coinbase.com/api/v1/';
    private $_apiKey;
    private $_rpc;

    public function __construct($apiKey)
    {
        $this->_apiKey = $apiKey;
        $this->_rpc = new Coinbase_Rpc($this->_apiKey);
    }

    public function getBalance()
    {
        return $this->_rpc->request("GET", "account/balance", array());
    }

    public function getReceiveAddress()
    {
        return $this->_rpc->request("GET", "account/receive_address", array())->address;
    }

    public function sendMoney($to, $amount, $notes=null, $userFee=null, $amountCurrency=null)
    {
        $params = array( "transaction[to]" => $to );

        if($amountCurrency !== null) {
            $params["transaction[amount_string]"] = $amount;
            $params["transaction[amount_currency_iso]"] = $amountCurrency;
        } else {
            $params["transaction[amount]"] = $amount;
        }

        if($notes !== null) {
            $params["transaction[notes]"] = $notes;
        }

        if($userFee !== null) {
            $params["transaction[user_fee]"] = $userFee;
        }

        return $this->_rpc->request("POST", "transactions/send_money", $params);
    }
}
