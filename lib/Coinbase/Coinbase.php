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

    public function get($method, $params)
    {
        return $this->_rpc->request("GET", $method, $params);
    }

    public function post($method, $params)
    {
        return $this->_rpc->request("POST", $method, $params);
    }

    public function delete($method, $params)
    {
        return $this->_rpc->request("DELETE", $method, $params);
    }

    public function put($method, $params)
    {
        return $this->_rpc->request("PUT", $method, $params);
    }

    public function getBalance()
    {
        return $this->get("account/balance", array());
    }

    public function getReceiveAddress()
    {
        return $this->get("account/receive_address", array())->address;
    }

    public function generateReceiveAddress($callback=null)
    {
        $params = array();
        if($callback !== null) {
            $params['address[callback_url]'] = $callback;
        }
        return $this->post("account/generate_receive_address", $params)->address;
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

        return $this->post("transactions/send_money", $params);
    }
}
