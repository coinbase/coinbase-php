<?php

class Coinbase
{
    const API_BASE = 'https://coinbase.com/api/v1/';
    private $_rpc;
    private $_authentication;


    public static function withApiKey($key, $secret)
    {
        return new Coinbase(new Coinbase_ApiKeyAuthentication($key, $secret));
    }

    public static function withSimpleApiKey($key)
    {
        return new Coinbase(new Coinbase_SimpleApiKeyAuthentication($key));
    }

    public static function withOAuth($oauth, $tokens)
    {
        return new Coinbase(new Coinbase_OAuthAuthentication($oauth, $tokens));
    }

    // This constructor is deprecated.
    public function __construct($authentication, $tokens=null, $apiKeySecret=null)
    {
        // First off, check for a legit authentication class type
        if (is_a($authentication, 'Coinbase_Authentication')) {
            $this->_authentication = $authentication;
        } else {
            // Here, $authentication was not a valid authentication object, so
            // analyze the constructor parameters and return the correct object.
            // This should be considered deprecated, but it's here for backward compatibility.
            // In older versions of this library, the first parameter of this constructor
            // can be either an API key string or an OAuth object.
            if ($tokens !== null) {
                $this->_authentication = new Coinbase_OAuthAuthentication($authentication, $tokens);
            } else if ($authentication !== null && is_string($authentication)) {
                $apiKey = $authentication;
                if ($apiKeySecret === null) {
                    // Simple API key
                    $this->_authentication = new Coinbase_SimpleApiKeyAuthentication($apiKey);
                } else {
                    $this->_authentication = new Coinbase_ApiKeyAuthentication($apiKey, $apiKeySecret);
                }
            } else {
                throw new Coinbase_ApiException('Could not determine API authentication scheme');
            }
        }

        $this->_rpc = new Coinbase_Rpc(new Coinbase_Requestor(), $this->_authentication);
    }

    // Used for unit testing only
    public function setRequestor($requestor)
    {
        $this->_rpc = new Coinbase_Rpc($requestor, $this->_authentication);
        return $this;
    }

    public function get($path, $params=array())
    {
        return $this->_rpc->request("GET", $path, $params);
    }

    public function post($path, $params=array())
    {
        return $this->_rpc->request("POST", $path, $params);
    }

    public function delete($path, $params=array())
    {
        return $this->_rpc->request("DELETE", $path, $params);
    }

    public function put($path, $params=array())
    {
        return $this->_rpc->request("PUT", $path, $params);
    }

    private function getPaginatedResource($resource, $listElement, $unwrapElement, $page=0, $params=array())
    {
        $result = $this->get($resource, array_merge(array( "page" => $page ), $params));
        $elements = array();
        foreach($result->{$listElement} as $element) {
            $elements[] = $element->{$unwrapElement}; // Remove one layer of nesting
        }

        $returnValue = new stdClass();
        $returnValue->total_count = $result->total_count;
        $returnValue->num_pages = $result->num_pages;
        $returnValue->current_page = $result->current_page;
        $returnValue->{$listElement} = $elements;
        return $returnValue;
    }

    public function getBalance()
    {
        return $this->get("account/balance", array())->amount;
    }

    public function getReceiveAddress()
    {
        return $this->get("account/receive_address", array())->address;
    }

    public function getAllAddresses($query=null, $page=0, $limit=null)
    {
        $params = array();
        if ($query !== null) {
            $params['query'] = $query;
        }
        if ($limit !== null) {
            $params['limit'] = $limit;
        }
        return $this->getPaginatedResource("addresses", "addresses", "address", $page, $params);
    }

    public function generateReceiveAddress($callback=null, $label=null)
    {
        $params = array();
        if($callback !== null) {
            $params['address[callback_url]'] = $callback;
        }
        if($label !== null) {
            $params['address[label]'] = $label;
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
            "name" => $name,
            "price_string" => $price,
            "price_currency_iso" => $currency
        );
        if($custom !== null) {
            $params['custom'] = $custom;
        }
        foreach($options as $option => $value) {
            $params[$option] = $value;
        }

        return $this->createButtonWithOptions($params);
    }

    public function createButtonWithOptions($options=array())
    {

        $response = $this->post("buttons", array( "button" => $options ));

        if(!$response->success) {
            return $response;
        }

        $returnValue = new stdClass();
        $returnValue->button = $response->button;
        $returnValue->embedHtml = "<div class=\"coinbase-button\" data-code=\"" . $response->button->code . "\"></div><script src=\"https://coinbase.com/assets/button.js\" type=\"text/javascript\"></script>";
        $returnValue->success = true;
        return $returnValue;
    }

    public function createOrderFromButtonCode($buttonCode)
    {
        return $this->post("buttons/" . $buttonCode . "/create_order");
    }

    public function createUser($email, $password)
    {
        return $this->post("users", array(
            "user[email]" => $email,
            "user[password]" => $password,
        ));
    }

    public function buy($amount, $agreeBtcAmountVaries=false)
    {
        return $this->post("buys", array(
            "qty" => $amount,
            "agree_btc_amount_varies " => $agreeBtcAmountVaries,
        ));
    }

    public function sell($amount)
    {
        return $this->post("sells", array(
            "qty" => $amount,
        ));
    }

    public function getContacts($query=null, $page=0, $limit=null)
    {
        $params = array(
            "page" => $page,
        );
        if ($query !== null) {
            $params['query'] = $query;
        }
        if ($limit !== null) {
            $params['limit'] = $limit;
        }

        $result = $this->get("contacts", $params);
        $contacts = array();
        foreach($result->contacts as $contact) {
            if(trim($contact->contact->email) != false) { // Check string not empty
                $contacts[] = $contact->contact->email;
            }
        }

        $returnValue = new stdClass();
        $returnValue->total_count = $result->total_count;
        $returnValue->num_pages = $result->num_pages;
        $returnValue->current_page = $result->current_page;
        $returnValue->contacts = $contacts;
        return $returnValue;
    }

    public function getCurrencies()
    {
        $response = $this->get("currencies", array());
        $result = array();
        foreach ($response as $currency) {
            $currency_class = new stdClass();
            $currency_class->name = $currency[0];
            $currency_class->iso = $currency[1];
            $result[] = $currency_class;
        }
        return $result;
    }

    public function getExchangeRate($from=null, $to=null)
    {
        $response = $this->get("currencies/exchange_rates", array());

        if ($from !== null && $to !== null) {
            return $response->{"{$from}_to_{$to}"};
        } else {
            return $response;
        }
    }

    public function getTransactions($page=0)
    {
        return $this->getPaginatedResource("transactions", "transactions", "transaction", $page);
    }

    public function getOrders($page=0)
    {
        return $this->getPaginatedResource("orders", "orders", "order", $page);
    }

    public function getTransfers($page=0)
    {
        return $this->getPaginatedResource("transfers", "transfers", "transfer", $page);
    }

    public function getBuyPrice($qty=1)
    {
        return $this->get("prices/buy", array( "qty" => $qty ))->amount;
    }

    public function getSellPrice($qty=1)
    {
        return $this->get("prices/sell", array( "qty" => $qty ))->amount;
    }

    public function getTransaction($id)
    {
        return $this->get("transactions/" . $id, array())->transaction;
    }

    public function getOrder($id)
    {
        return $this->get("orders/" . $id, array())->order;
    }

    public function getUser()
    {
        return $this->get("users", array())->users[0]->user;
    }

}
