<?php

namespace Rho\CircuitBreaker;

class SimpleCircuitBreaker {
    const CLOSED = 0;
    const OPEN = 1;
    const HALF_OPEN = 2;

    protected $failThreshold = 3;
    protected $resetTime = 5; // seconds
    protected $fails = 0;

    public static function wrap($obj, $opts = []) {
        return new SimpleCircuitBreaker($obj, $opts);
    }

    public function __construct($circuit, $opts = []) {
        if(isset($opts['failThreshold'])) {
            $this->failThreshold = $opts['failThreshold'];
        }

        if(isset($opts['resetTime'])) {
            $this->resetTime = $opts['resetTime'];
        }

        $this->circuit = $circuit;
        $this->opts = $opts;
    }

    public function __call($name, $args) {
        switch($this->circuitState()) {
            case self::CLOSED:
            case self::HALF_OPEN:
                try {
                    $result = call_user_func_array([$this->circuit, $name], $args);
                    $this->circuitBreakerReset();
                    return $result;
                } catch (\Exception $e) {
                    $this->circuitRecordFail();
                    throw $e;
                }
                break;
            case self::OPEN:
                throw new CircuitBreakerOpenException();
                break;
        }
    }

    protected function circuitState() {
        if($this->circuitTooManyFails() && $this->circuitEnoughTimeHasPassed()) {
            return self::HALF_OPEN;
        } else if($this->circuitTooManyFails()) {
            return self::OPEN;
        } else {
            return self::CLOSED;
        }
    }

    protected function circuitRecordFail() {
        $this->fails++;
        $this->lastFailTime = microtime(true);
    }

    protected function circuitTooManyFails(): bool {
        return $this->fails >= $this->failThreshold;
    }

    protected function circuitEnoughTimeHasPassed(): bool {
        return microtime(true) - $this->lastFailTime > $this->resetTime;
    }

    protected function circuitBreakerReset() {
        $this->fails = 0;
        $this->lastFailTime = null;
    }
}
