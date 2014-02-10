<?php
require_once(dirname(__FILE__) . '/../lib/Coinbase.php');

// Create an API key at https://coinbase.com/account/api and set these values accordingly
$_API_KEY = "[redacted]";
$_API_SECRET = "[redacted]";

$coinbase = Coinbase::withApiKey($_API_KEY, $_API_SECRET);
echo 'Balance: ' . $coinbase->getBalance() . '<br>';
echo $coinbase->createButton("Alpaca socks", "10.00", "CAD")->embedHtml;