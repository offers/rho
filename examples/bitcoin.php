#!/usr/bin/env php
<?php

/*
 * Get a current quote for Bitcoin in USD.
 * Uses a Circuit Breaker to limit failures to 3 in 10s.
 * Uses a Retrier to try at most 10 times or the breaker opens.
 */

require "../vendor/autoload.php";

use Rho\Exception;
use Rho\Transport\HttpJsonTransport;
use Rho\CircuitBreaker\SimpleCircuitBreaker;
use Rho\Retrier;
use Rho\RateLimiter\SimpleRateLimiter;

function showException($e) {
    echo $e->getMessage() . "\n";
}

$client = new HttpJsonTransport("http://api.coindesk.com");
$client = SimpleRateLimiter::wrap($client, ['limits' => [5 => 2]]); // limit to 2 requests every 5s
$client = SimpleCircuitBreaker::wrap($client, ['failThreshold' => 3, 'resetTime' => 10]);
$client = Retrier::wrap($client, ['retries' => 10, 'delay' => 1000]); // delay in ms

try {
    $resp = $client->rpc(['GET', '/v1/bpi/currentprice/USD.json'], [], ['timeout' => 3, 'connect_timeout' => 3]);
    if(!$resp->isError()) {
        $data = $resp->getResult();
        echo money_format("Current Bitcoin price in USD is $%i\n", $data['bpi']['USD']['rate']);
    } else {
        echo "API Server error\n";
    }
} catch(Rho\Exception\CircuitBreakerOpenException $e) {
    showException($e);
} catch(Rho\Exception\TooManyRetriesException $e) {
    showException($e);
}
