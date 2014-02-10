<?php

class Coinbase_Rpc
{
    private $_requestor;
    private $_coinbase;

    public function __construct($requestor, $coinbase)
    {
        $this->_requestor = $requestor;
        $this->_coinbase = $coinbase;
    }

    public function request($method, $url, $params)
    {

        $auth = $this->_coinbase->getAuthenticationData();
        if($auth->useSimpleApiKey) {
            // Always set the api_key parameter to the API key
            $params['api_key'] = $auth->apiKey;
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
            if ($queryString) {
                $url .= "?" . $queryString;
            }
        } else if ($method == 'post') {
            $curlOpts[CURLOPT_POST] = 1;
            $curlOpts[CURLOPT_POSTFIELDS] = $queryString;
        } else if ($method == 'delete') {
            $curlOpts[CURLOPT_CUSTOMREQUEST] = "DELETE";
            if ($queryString) {
                $url .= "?" . $queryString;
            }
        } else if ($method == 'put') {
            $curlOpts[CURLOPT_CUSTOMREQUEST] = "PUT";
            $curlOpts[CURLOPT_POSTFIELDS] = $queryString;
        }

        // Headers
        $headers = array('User-Agent: CoinbasePHP/v1');

        if ($auth->useOauth) {
            // Use OAuth
            if(time() > $auth->tokens["expire_time"]) {
                throw new Coinbase_TokensExpiredException("The OAuth tokens are expired. Use refreshTokens to refresh them");
            }

            $headers[] = 'Authorization: Bearer ' . $auth->tokens["access_token"];
        } else if (!$auth->useSimpleApiKey) {
            // Use HMAC API key
            $microseconds = sprintf('%0.0f',round(microtime(true) * 1000000));

            $dataToHash =  $microseconds . $url;
            if (array_key_exists(CURLOPT_POSTFIELDS, $curlOpts)) {
                $dataToHash .= $curlOpts[CURLOPT_POSTFIELDS];
            }
            $signature = hash_hmac("sha256", $dataToHash, $auth->apiKey[1]);

            $headers[] = "ACCESS_KEY: {$auth->apiKey[0]}";
            $headers[] = "ACCESS_SIGNATURE: $signature";
            $headers[] = "ACCESS_NONCE: $microseconds";
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
