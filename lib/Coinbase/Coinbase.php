<?php

class Coinbase
{
    const API_BASE = 'https://coinbase.com/api/v1/';
    private $_apiKey;
    private $_rpc;

    public function __construct($apiKey, $requestor=null)
    {
        $this->_apiKey = $apiKey;

        if($requestor === null) {
            $requestor = new Coinbase_Requestor();
        }

        $this->_rpc = new Coinbase_Rpc($requestor, $this->_apiKey);
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
        return $this->get("account/balance", array())->amount;
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

    public function requestMoney($from, $amount, $notes=null, $amountCurrency=null)
    {
        $params = array( "transaction[from]" => $from );

        if($amountCurrency !== null) {
            $params["transaction[amount_string]"] = $amount;
            $params["transaction[amount_currency_iso]"] = $amountCurrency;
        } else {
            $params["transaction[amount]"] = $amount;
        }

        if($notes !== null) {
            $params["transaction[notes]"] = $notes;
        }

        return $this->post("transactions/request_money", $params);
    }

    public function resendRequest($id)
    {
        return $this->put("transactions/" . $id . "/resend_request", array());
    }

    public function cancelRequest($id)
    {
        return $this->delete("transactions/" . $id . "/cancel_request", array());
    }

    public function completeRequest($id)
    {
        return $this->put("transactions/" . $id . "/complete_request", array());
    }

    public function createButton($name, $price, $currency, $custom=null, $options=array())
    {

        $params = array(
            "button[name]" => $name,
            "button[price_string]" => $price,
            "button[price_currency_iso]" => $currency
        );
        if($custom !== null) {
            $params['button[custom]'] = $custom;
        }
        foreach($options as $option => $value) {
            $params["button[$option]"] = $value;
        }

        $response = $this->post("buttons", $params);

        if(!$response->success) {
            return $response;
        }

        $returnValue = new stdClass();
        $returnValue->button = $response->button;
        $returnValue->embedHtml = "<div class=\"coinbase-button\" data-code=\"" . $response->button->code . "\"></div><script src=\"https://coinbase.com/assets/button.js\" type=\"text/javascript\"></script>";
        $returnValue->success = true;
        return $returnValue;
    }
}
