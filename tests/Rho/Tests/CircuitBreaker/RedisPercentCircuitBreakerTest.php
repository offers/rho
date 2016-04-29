<?php
//TODO unify with the regular percent cb test to keep DRY

namespace Rho\Tests;

use Rho;
use Rho\CircuitBreaker;
use Rho\CircuitBreaker\RedisPercentCircuitBreaker;
use Rho\Exception\CircuitBreakerOpenException;
use \Predis;

class RedisPercentCircuitBreakerTest extends \PHPUnit_Framework_TestCase {
    public function setUp() {
        $this->redis = new Predis\Client([
            'host' => 'redis'
        ]);

        $this->opts = ['redis' => $this->redis, 'name' => 'testcircuit'];

        $this->redis->flushdb();
    }

    public function testCircuitStartsClosed() {
        $stub = $this->getMockBuilder('FakeCircuit')
                     ->setMethods(['foo'])
                     ->getMock();
        $stub->method('foo')
             ->willReturn('bar');

        $cb = new RedisPercentCircuitBreaker($stub, $this->opts);
        $this->assertEquals('bar', $cb->foo());
    }

    // after 1 failure the circuit should still be closed
    public function testCircuitStaysClosed() {
        $stub = $this->getMockBuilder('FakeCircuit')
                     ->setMethods(['foo'])
                     ->getMock();
        $stub->method('foo')
             ->will($this->throwException(new \Exception));

        $cb = new RedisPercentCircuitBreaker($stub, $this->opts);
        $this->expectException(\Exception::class);
        $cb->foo();

        // successful method call should succeed
        $stub->method('foo')
             ->willReturn('bar');
        $this->assertEquals('bar', $cb->foo());
    }

    // after many failures the circuit should open
    public function testCircuitOpens() {
        $stub = $this->getMockBuilder('FakeCircuit')
                     ->setMethods(['foo'])
                     ->getMock();
        $stub->method('foo')
             ->will($this->throwException(new \Exception("foo exception")));

        $cb = new RedisPercentCircuitBreaker($stub, array_merge($this->opts, ['minCalls' => 4]));
        for($i = 0; $i < 10; $i++) {
            try {
                $cb->foo();
            } catch (\Exception $e) {}
        }
        
        // successful method call should fail with Circuit Open
        $stub->method('foo')
             ->willReturn('bar');
        $this->expectException(CircuitBreakerOpenException::class);
        $cb->foo();
    }

    //TODO test time passing resets circuit breaker
}
