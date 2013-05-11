<?php

class Coinbase_Rpc
{
    private $_apiKey;

    public function __construct($apiKey=null)
    {
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
        $response = curl_exec($curl);

        // Check for errors
        if($response === false) {
            $error = curl_errno($curl);
            $message = curl_error($curl);
            curl_close($curl);
            throw new Coinbase_ConnectionException("Network error " . $message . " (" . $error . ")");
        }

        // Check status code
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if($statusCode != 200) {
            throw new Coinbase_ApiException("Status code " . $statusCode, $statusCode, $response);
        }

        // Decode response
        try {
            $json = json_decode($response);
        } catch (Exception $e) {
            throw new Coinbase_ConnectionException("Invalid response body", $statusCode, $response);
        }

        return $json;
    }
}
