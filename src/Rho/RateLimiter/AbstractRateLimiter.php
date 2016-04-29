<?php

namespace Rho;

abstract class AbstractRateLimiter {
    use HasLogger;

    public function __construct($obj, $opts = []) {
        $this->obj = $obj;
        $this->opts = $opts;
    }

    public function __call($name, $args) {
        $result = call_user_func_array([$this->obj, $name], $args);
        return $result;
    }
}
