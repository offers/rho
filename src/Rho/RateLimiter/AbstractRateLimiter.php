<?php

namespace Rho\RateLimiter;

use Rho;

abstract class AbstractRateLimiter {
    use Rho\HasLogger;

    public function __construct($obj, $opts = []) {
        $this->obj = $obj;
        $this->opts = $opts;
    }

    abstract public function __call($name, $args);
}
