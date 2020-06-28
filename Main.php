<?php
include 'CardmarketConnection.php';
$method = 'POST';
$url = 'https://api.cardmarket.com/ws/v2.0/stock';
$xml = null; //use this line if no XML is provided eg. most GET commands
$xml = file_get_contents('Request.xml');
echo "$xml\n";
$cardmarketConnection = new CardmarketConnection($method, $url, $xml);
$cardmarketConnection->execHTTPRequest();
?>