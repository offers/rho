<?php

namespace Rho\Tests;

use Rho;
use Rho\Transport;
use Rho\NullLogger;
use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\Middleware;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

class HttpJsonTransportTest extends \PHPUnit_Framework_TestCase {
    public function makeMock($mock) {
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        return new FakeHttpJsonTransport($client, 'http://localhost');
    }

    public function testRpcSuccess() {
        $expected = ['foo' => 'bar'];
        $json = json_encode($expected);
        $mock = new MockHandler([
            new GuzzleHttp\Psr7\Response(200, [], $json)
        ]);
        $http = $this->makeMock($mock);

        $resp = $http->rpc(['GET', '/example'], []);
        $this->assertInstanceOf('Rho\Response', $resp);
        $this->assertFalse($resp->isError());
        $this->assertEquals($expected, $resp->getResult());
    }

    public function testRpcArgs() {
        $args = ['foo' => 'bar'];
        $container = [];
        $history = Middleware::history($container);

        $stack = HandlerStack::create();
        $stack->push($history);

        $client = new Client(['handler' => $stack]);
        $t = new FakeHttpJsonTransport($client, 'http://localhost');

        // throws an exception since we didn't implement a response
        $this->expectException(Rho\TransportException::class);
        $t->rpc(['GET', '/example'], $args);

        $this->assertCount(1, $container);
        $transaction = $container[0];
        $req = $transaction['request'];
        $this->assertEquals('GET', $req->getMethod());
        $this->assertEquals($args, $transaction['options']['query']);
    }

    public function testRpcJsonDecodeFail() {
        $json = "malformed";
        $mock = new MockHandler([
            new GuzzleHttp\Psr7\Response(200, [], $json)
        ]);
        $http = $this->makeMock($mock);

        $resp = $http->rpc(['GET', '/example'], []);
        $this->assertInstanceOf('Rho\ErrorResponse', $resp);
        $this->assertTrue($resp->isError());
    }

    public function testRpcTransportFail() {
        $method = 'GET';
        $endpoint = '/test';
        $mock = new MockHandler([
            new GuzzleHttp\Exception\RequestException("Error Communicating with Server", new GuzzleHttp\Psr7\Request($method, $endpoint))
        ]);
        $http = $this->makeMock($mock);

        $this->expectException(Rho\TransportException::class);
        $resp = $http->rpc([$method, $endpoint], []);
    }
}

class FakeHttpJsonTransport extends Transport\HttpJsonTransport {
    use Rho\HasLogger;

    public function __construct($client, $server) {
        $this->client = $client;
        $this->setServer($server);
        return $this;
    }
}
