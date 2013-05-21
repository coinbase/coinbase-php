<?php

class Coinbase_Rpc
{
    private $_requestor;
    private $_apiKey;
    private $_oauthObject;
    private $_oauthTokens;

    public function __construct($requestor, $apiKey=null, $oauthObject=null, $oauthTokens=null)
    {
        $this->_requestor = $requestor;
        $this->_apiKey = $apiKey;
        $this->_oauthObject = $oauthObject;
        $this->_oauthTokens = $oauthTokens;
    }

    public function request($method, $url, $params)
    {

        if($this->_apiKey !== null) {
            // Always set the api_key parameter to the API key
            $params['api_key'] = $this->_apiKey;
        }

        // Create query string
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
            $curlOpts[CURLOPT_CUSTOMREQUEST] = "DELETE";
            $url .= "?" . $queryString;
        } else if ($method == 'put') {
            $curlOpts[CURLOPT_CUSTOMREQUEST] = "PUT";
            $curlOpts[CURLOPT_POSTFIELDS] = $queryString;
        }

        // Headers
        $headers = array('User-Agent: CoinbasePHP/v1');

        if($this->_oauthObject !== null) {
            // Use OAuth
            if(time() > $this->_oauthTokens["expire_time"]) {
                throw new Coinbase_TokensExpiredException("The OAuth tokens are expired. Use refreshTokens to refresh them");
            }

            $headers[] = 'Authorization: Bearer ' . $this->_oauthTokens["access_token"];
        }

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
        if($json === null) {
            throw new Coinbase_ApiException("Invalid response body", $response['statusCode'], $response['body']);
        }
        if(isset($json->error)) {
            throw new Coinbase_ApiException($json->error, $response['statusCode'], $response['body']);
        } else if(isset($json->errors)) {
            throw new Coinbase_ApiException(implode($json->errors, ', '), $response['statusCode'], $response['body']);
        }

        return $json;
    }
}
