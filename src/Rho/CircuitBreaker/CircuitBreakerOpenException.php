<?php

namespace Rho\CircuitBreaker;

class CircuitBreakerOpenException extends \Exception {
    public function __construct() {
        parent::__construct("Circuit breaker is open");
    }
}
