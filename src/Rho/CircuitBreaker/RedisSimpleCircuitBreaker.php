<?php

namespace Rho\CircuitBreaker;

class RedisSimpleCircuitBreaker extends SimpleCircuitBreaker {
    const PREFIX = "cb";

    public static function wrap($obj) {
        return new RedisSimpleCircuitBreaker($obj);
    }

    protected function circuitRecordFail() {
        $this->incr('fails');
        $this->set('lastFail', microtime(true));
    }

    protected function circuitTooManyFails(): bool {
        return $this->get('fails') >= $this->failThreshold;
    }

    protected function circuitEnoughTimeHasPassed(): bool {
        return microtime(true) - $this->get('lastFail') > $this->resetTime;
    }

    protected function circuitBreakerReset() {
        $this->set('fails', 0);
        $this->set('lastFail', 0);
    }

    protected function get($k) {
        return $this->redis->get($this->key($k));
    }

    protected function set($k, $v) {
        return $this->redis->set($this->key($k), $v);
    }

    protected function incr($k) {
        return $this->redis->incr($this->key($k));
    }

    protected function key($k) {
        return implode(':', [self::PREFIX, $this->name, $k]);
    }
}
