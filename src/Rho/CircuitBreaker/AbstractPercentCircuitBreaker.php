<?php

namespace Rho\CircuitBreaker;

use Rho;

abstract class AbstractPercentCircuitBreaker {
    use Rho\HasLogger;

    const CLOSED = 0;
    const OPEN = 1;

    protected $failThreshold = 0.5;
    protected $timePeriod = 15; // seconds
    protected $minCalls = 1;

    abstract static function wrap($obj);

    public function __construct($obj, $opts = []) {
        $this->obj = $obj;
        $this->opts = $opts;

        if(isset($opts['failThreshold'])) {
            $this->failThreshold = $opts['failThreshold'];
        }

        if(isset($opts['timePeriod'])) {
            $this->timePeriod = $opts['timePeriod'];
        }

        if(isset($opts['minCalls'])) {
            $this->minCalls = $opts['minCalls'];
            if($this->minCalls <= 0) {
                throw new InvalidArgumentException("minCalls must be > 0");
            }
        }
    }

    abstract function __call($name, $args);
}
