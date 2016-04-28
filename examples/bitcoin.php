#!/usr/bin/env php
<?php

require "../vendor/autoload.php";

use \Rho\Transport\HttpJsonTransport;
use \Rho\CircuitBreaker\SimpleCircuitBreaker;

$http = new HttpJsonTransport("http://api.coindesk.com");
$client = SimpleCircuitBreaker::wrap($http);

try {
    $resp = $client->rpc(['GET', '/v1/bpi/currentprice/USD.json']);
    if(!$resp->isError()) {
        $data = $resp->getResult();
        echo money_format("Current Bitcoin price in USD is $%i\n", $data['bpi']['USD']['rate']);
    } else {
        echo "API Server error\n";
    }
} catch(Rho\TransportException $e) {
    echo $e->getMessage();
} catch(Rho\CircuitBreakerOpenException $e) {
    echo "CircuitBreaker open\n";
    echo $e->getMessage();
}

