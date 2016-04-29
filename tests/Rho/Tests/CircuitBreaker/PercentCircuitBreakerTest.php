<?php

namespace Rho\Tests;

use Rho;
use Rho\CircuitBreaker;
use Rho\CircuitBreaker\PercentCircuitBreaker;
use Rho\Exception\CircuitBreakerOpenException;

class PercentCircuitBreakerTest extends \PHPUnit_Framework_TestCase {
    public function testCircuitStartsClosed() {
        $stub = $this->getMockBuilder('FakeCircuit')
                     ->setMethods(['foo'])
                     ->getMock();
        $stub->method('foo')
             ->willReturn('bar');

        $cb = new PercentCircuitBreaker($stub);
        $this->assertEquals('bar', $cb->foo());
    }

    // after 1 failure the circuit should still be closed
    public function testCircuitStaysClosed() {
        $stub = $this->getMockBuilder('FakeCircuit')
                     ->setMethods(['foo'])
                     ->getMock();
        $stub->method('foo')
             ->will($this->throwException(new \Exception));

        $cb = new PercentCircuitBreaker($stub);
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

        $cb = new PercentCircuitBreaker($stub, ['minCalls' => 4]);
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
