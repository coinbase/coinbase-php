<?php

class Coinbase_OAuth
{
    private $_clientId;
    private $_clientSecret;
    private $_redirectUri;

    public function __construct($clientId, $clientSecret, $redirectUri)
    {
        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
        $this->_redirectUri = $redirectUri;
    }

    public function createAuthorizeUrl($scope)
    {
        $url = "https://coinbase.com/oauth/authorize?response_type=code" .
            "&client_id=" . urlencode($this->_clientId) .
            "&redirect_uri=" . urlencode($this->_redirectUri) .
            "&scope=" . $scope;

        foreach(func_get_args() as $key => $scope)
        {
            if(0 == $key) {
                // First scope was already appended
            } else {
                $url .= "+" . urlencode($scope);
            }
        }

        return $url;
    }

    public function refreshTokens($oldTokens)
    {
        return $this->getTokens($oldTokens["refresh_token"], "refresh_token");
    }

    public function getTokens($code, $grantType='authorization_code')
    {
        $postFields["grant_type"] = $grantType;
        $postFields["redirect_uri"] = $this->_redirectUri;
        $postFields["client_id"] = $this->_clientId;
        $postFields["client_secret"] = $this->_clientSecret;

        if("refresh_token" === $grantType) {
            $postFields["refresh_token"] = $code;
        } else {
            $postFields["code"] = $code;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postFields));
        curl_setopt($curl, CURLOPT_URL, 'https://coinbase.com/oauth/token');
        curl_setopt($curl, CURLOPT_CAINFO, dirname(__FILE__) . '/ca-coinbase.crt');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('User-Agent: CoinbasePHP/v1'));

        $response = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if($response === false) {
            $error = curl_errno($curl);
            $message = curl_error($curl);
            curl_close($curl);
            throw new Coinbase_ConnectionException("Could not get tokens - network error " . $message . " (" . $error . ")");
        }
        if($statusCode !== 200) {
            throw new Coinbase_ApiException("Could not get tokens - code " . $statusCode, $statusCode, $response);
        }
        curl_close($curl);

        try {
            $json = json_decode($response);
        } catch (Exception $e) {
            throw new Coinbase_ConnectionException("Could not get tokens - JSON error", $statusCode, $response);
        }

        return array(
            "access_token" => $json->access_token,
            "refresh_token" => $json->refresh_token,
            "expire_time" => time() + 7200 );
    }
}
