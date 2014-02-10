<?php
require_once(dirname(__FILE__) . '/../lib/Coinbase.php');

// Create an application at https://coinbase.com/oauth/applications and set these values accordingly
$_CLIENT_ID = "83a481f96bf28ea4bed1ee8bdc49ba4265609efa40d40477c2a57e913c479065";
$_CLIENT_SECRET = "a8dda20b94d09e84e8fefa5e7560133d9c5af9da93ec1d3e79ad0843d2920bbb";

// Note: your redirect URL should use HTTPS.
$_REDIRECT_URL = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

$coinbaseOauth = new Coinbase_OAuth($_CLIENT_ID, $_CLIENT_SECRET, $_REDIRECT_URL);

if(isset($_GET['code'])) {

    // Request tokens
    $tokens = $coinbaseOauth->getTokens($_GET['code']);

    // The user is now authenticated! Access and refresh tokens are in $tokens
    // Store these tokens safely, and use them to make Coinbase API requests in the future.
    // For example:
    $coinbase = Coinbase::withOauth($coinbaseOauth, $tokens);

    try {
        echo 'Balance: ' . $coinbase->getBalance() . '<br>';
        echo $coinbase->createButton("Alpaca socks", "10.00", "CAD")->embedHtml;
    } catch (Coinbase_TokensExpiredException $e) {
        $newTokens = $coinbaseOauth->refreshTokens($tokens);
        // Store $newTokens and retry request
    }
} else {

    // Redirect to Coinbase authorization page
    // The provided parameters specify the access your application will have to the
    // user's account; for a full list, see https://coinbase.com/docs/api/overview
    // You can pass as many scopes as you would like
    echo "<a href=\"" . $coinbaseOauth->createAuthorizeUrl("balance", "buttons") . "\">Connect with Coinbase</a>";
}