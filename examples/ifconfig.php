#!/usr/bin/env php
<?php

/*
 * ifconfig.me was useful, but these days it almost always times out.
 * Use Rho to infinitely retry getting our IP from ifconfig.me,
 * until the script is killed with Ctrl+C
 */

require "../vendor/autoload.php";

use \Rho\Transport\HttpJsonTransport;
use \Rho\Retrier;

function showException($e) {
    echo $e->getMessage() . "\n";
}

$client = new HttpJsonTransport("http://ifconfig.me");
$client = Retrier::wrap($client);

try {
    $resp = $client->rpc(['GET', '/all.json'], [], ['timeout' => 5, 'connect_timeout' => 5]);
    if(!$resp->isError()) {
        $data = $resp->getResult();
        echo "Your IP is {$data['ip_addr']}\n";
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

