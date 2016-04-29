#!/usr/bin/env php
<?php

/*
 * ifconfig.me was useful, but these days it almost always times out.
 * Use Rho to infinitely retry getting our IP from ifconfig.me,
 * until the script is killed with Ctrl+C
 */

require "../vendor/autoload.php";

use Rho\Exception;
use Rho\Transport\HttpJsonTransport;
use Rho\Retrier;
use Monolog\Logger; 

function showException($e) {
    echo $e->getMessage() . "\n";
}

$logger = new Logger('ifconfig');
$client = new HttpJsonTransport("http://ifconfig.me", ['logger' => $logger]);
$client = Retrier::wrap($client, ['logger' => $logger]);

try {
    $resp = $client->rpc(['GET', '/all.json'], [], ['timeout' => 5, 'connect_timeout' => 5]);
    if(!$resp->isError()) {
        $data = $resp->getResult();
        echo "Your IP is {$data['ip_addr']}\n";
    } else {
        echo "API Server error\n";
    }
} catch(Rho\Exception\TooManyRetriesException $e) {
    showException($e);
}

