<?php

namespace Rho\RateLimiter;

use Rho;
use Rho\Exception\OverRateLimitException;

class SimpleRateLimiter extends AbstractRateLimiter {
    protected $buckets = [];

    public static function wrap($obj, $opts = []) {
        return new SimpleRateLimiter($obj, $opts);
    } 

    public function __construct($obj, $opts = []) {
        parent::__construct($obj, $opts);
        $this->initBuckets();
    }

    public function __call($name, $args) {
        if($this->overAnyLimit()) {
            throw new OverRateLimitException();
        }

        return call_user_func_array([$this->obj, $name], $args);
    }

    protected function initBuckets() {
        foreach($this->limits as $duration => $_) {
            $this->buckets[$duration] = [time(), 0];
        }
    }

    protected function overAnyLimit() {
        $over = false;
        foreach($this->limits as $duration => $limit) {
            if($this->overLimit($duration, $limit)) {
                $over = true;
            }
        }
        return $over;
    }

    protected function overLimit($duration, $limit) {
        $now = time();
        $start = $this->buckets[$duration][0];
        if($now - $duration > $start) {
            $this->buckets[$duration] = [$now, 0];
        }
        return ++$this->buckets[$duration][1] > $limit;        
    }
}
