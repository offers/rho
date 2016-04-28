<?php

namespace Rho;

class Retrier {
    protected $obj;
    protected $retries = 0;
    protected $backoff = 1.0;
    protected $delay = 100; // ms

    public static function wrap($obj, $opts = []) {
        return new Retrier($obj, $opts);
    }

    public function __construct($obj, $opts = []) {
        $this->obj = $obj;

        if(isset($opts['retries'])) {
            $this->retries = $opts['retries'];
        }
        
        if(isset($opts['backoff'])) {
            $this->backoff = $opts['backoff'];
        }

        if(isset($opts['delay'])) {
            $this->delay = $opts['delay'];
        }
    }

    public function __call($name, $args) {
        $r = 0;
        for(; 0 == $this->retries || $r < $this->retries; $r++) {
            try {
                return call_user_func_array([$this->obj, $name], $args);
            } catch(CircuitBreaker\CircuitBreakerOpenException $e) {
                throw $e;
            } catch(\Exception $e) {
                usleep($this->delay * (1000 * ($this->backoff ** $this->retries)));
            }
        }
        throw new TooManyRetriesException("$r retries for $name, max is {$this->retries}");
    }
}
