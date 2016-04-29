<?php

namespace Rho\RateLimiter;

class SimpleRateLimiter extends AbstractRateLimiter {
    public static function wrap($obj, $opts = []) {
        return new SimpleRateLimiter($obj, $opts);
    } 

    public function __call($name, $args) {
        $result = call_user_func_array([$this->obj, $name], $args);
        return $result;
    }
}
