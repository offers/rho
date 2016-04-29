<?php

namespace Rho\Tests;

use Rho;
use Rho\RateLimiter\SimpleRateLimiter;
use Rho\Exception\OverRateLimitException;

class SimpleRateLimiterTest extends \PHPUnit_Framework_TestCase {
    public function testWrapsObject() {
        $stub = $this->getMockBuilder('FakeObj')
                     ->setMethods(['foo'])
                     ->getMock();
        $stub->method('foo')
             ->willReturn('bar');

        $r = new SimpleRateLimiter($stub, ['limits' => [1 => 10]]);
        $this->assertEquals('bar', $r->foo());
    }

    public function testLimitsRate() {
        $stub = $this->getMockBuilder('FakeObj')
                     ->setMethods(['foo'])
                     ->getMock();
        $stub->method('foo')
             ->willReturn('bar');

        $r = new SimpleRateLimiter($stub, ['limits' => [10 => 1]]);
        $this->assertEquals('bar', $r->foo());
        $this->expectException(OverRateLimitException::class);
        $this->assertEquals('bar', $r->foo());
    }
}
