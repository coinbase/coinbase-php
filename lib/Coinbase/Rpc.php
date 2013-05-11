<?php

class Coinbase_Rpc
{
    private $_requestor;
    private $_apiKey;

    public function __construct($requestor, $apiKey=null)
    {
        $this->_requestor = $requestor;
        $this->_apiKey = $apiKey;
    }

    public function request($method, $url, $params)
    {
        // Create query string
        // Always set the api_key parameter to the API key
        $params['api_key'] = $this->_apiKey;
        $queryString = http_build_query($params);
        $url = Coinbase::API_BASE . $url;

        // Initialize CURL
        $curl = curl_init();
        $curlOpts = array();

        // HTTP method
        $method = strtolower($method);
        if ($method == 'get') {
            $curlOpts[CURLOPT_HTTPGET] = 1;
            $url .= "?" . $queryString;
        } else if ($method == 'post') {
            $curlOpts[CURLOPT_POST] = 1;
            $curlOpts[CURLOPT_POSTFIELDS] = $queryString;
        } else if ($method == 'delete') {
            // Unimplemented
        } else if ($method == 'put') {
            // Unimplemented
        }

        // Headers
        $headers = array('User-Agent: CoinbasePHP/v1');

        // CURL options
        $curlOpts[CURLOPT_URL] = $url;
        $curlOpts[CURLOPT_HTTPHEADER] = $headers;
        $curlOpts[CURLOPT_CAINFO] = dirname(__FILE__) . '/ca-coinbase.crt';
        $curlOpts[CURLOPT_RETURNTRANSFER] = true;

        // Do request
        curl_setopt_array($curl, $curlOpts);
        $response = $this->_requestor->doCurlRequest($curl);

        // Decode response
        try {
            $json = json_decode($response['body']);
        } catch (Exception $e) {
            throw new Coinbase_ConnectionException("Invalid response body", $response['statusCode'], $response['body']);
        }
        if(isset($json->error)) {
            throw new Coinbase_ApiException($json->error, $response['statusCode'], $response['body']);
        } else if(isset($json->errors)) {
            throw new Coinbase_ApiException(implode($json->errors, ', '), $response['statusCode'], $response['body']);
        }

        return $json;
    }
}
