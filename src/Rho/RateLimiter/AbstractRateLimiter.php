<?php

namespace Rho\RateLimiter;

use Rho;

abstract class AbstractRateLimiter {
    use Rho\HasLogger;

    protected $limits;

    public function __construct($obj, $opts = []) {
        $this->obj = $obj;
        $this->opts = $opts;

        if(!isset($opts['limits']) || !is_array($opts['limits'])) {
            throw new \InvalidArgumentException("\$opts['limits'] must be set to an array");
        }

        $this->limits = $opts['limits'];
    }

    abstract public function __call($name, $args);
}
