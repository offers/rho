<?php

namespace Rho\Exception;

class CircuitBreakerOpenException extends \Exception {
    public function __construct() {
        parent::__construct("Circuit breaker is open");
    }
}
