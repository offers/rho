<?php

namespace Rho\CircuitBreaker;

use Rho;
use Rho\Exception\CircuitBreakerOpenException;

class PercentCircuitBreaker extends AbstractPercentCircuitBreaker {
    use Rho\HasLogger;

    protected $calls = 0;
    protected $fails = 0;
    protected $periodStart = 0;

    public static function wrap($obj) {
        return new PercentCircuitBreaker($obj);
    }

    public function __call($name, $args) {
        switch($this->circuitState()) {
            case self::CLOSED:
                $this->circuitRecordCall();
                try {
                    return call_user_func_array([$this->obj, $name], $args);
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
        $now = time();
        if($now - $this->periodStart >= $this->timePeriod) {
            $this->periodStart = $now;
            $this->calls = 0;
            $this->fails = 0;
        }

        if($this->calls >= $this->minCalls &&
           floatval($this->fails) / floatval($this->calls) >= $this->failThreshold) {
            return self::OPEN;
        }

        return self::CLOSED;
    }

    protected function circuitRecordCall() {
        $this->calls++;
    }

    protected function circuitRecordFail() {
        $this->fails++;
    }
}
