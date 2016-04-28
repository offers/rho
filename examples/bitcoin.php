#!/usr/bin/env php
<?php

/*
 * Get a current quote for Bitcoin in USD.
 * Uses a Circuit Breaker to limit failures to 3 in 10s.
 * Uses a Retrier to keep trying until successful or the breaker opens.
 */

require "../vendor/autoload.php";

use Rho\Transport\HttpJsonTransport;
use Rho\CircuitBreaker\SimpleCircuitBreaker;
use Rho\Retrier;

function showException($e) {
    echo $e->getMessage() . "\n";
}

$client = new HttpJsonTransport("http://api.coindesk.com");
$client = SimpleCircuitBreaker::wrap($client, ['failThreshold' => 3, 'resetTime' => 10]);
$client = Retrier::wrap($client);

try {
    $resp = $client->rpc(['GET', '/v1/bpi/currentprice/USD.json'], [], ['timeout' => 3, 'connect_timeout' => 3]);
    if(!$resp->isError()) {
        $data = $resp->getResult();
        echo money_format("Current Bitcoin price in USD is $%i\n", $data['bpi']['USD']['rate']);
    } else {
        echo "API Server error\n";
    }
} catch(Rho\TransportException $e) {
    showException($e);
} catch(Rho\CircuitBreaker\CircuitBreakerOpenException $e) {
    showException($e);
} catch(Rho\TooManyRetriesException $e) {
    showException($e);
}
