<?php

namespace Rho;

use Rho;
use Rho\Exception\{CircuitBreakerOpenException,TooManyRetriesException};

class Retrier {
    use HasLogger;

    protected $obj;
    protected $retries = 0;
    protected $backoff = 1.0;
    protected $delay = 100; // ms

    public static function wrap($obj, $opts = []) {
        return new Retrier($obj, $opts);
    }

    public function __construct($obj, $opts = []) {
        $this->obj = $obj;
        $this->opts = $opts;

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
        for(; 0 == $this->retries || $r <= $this->retries; $r++) {
            $this->_logger()->debug("try $r", ['func' => $name, 'args' => $args]);
            try {
                return call_user_func_array([$this->obj, $name], $args);
            } catch(CircuitBreakerOpenException $e) {
                $this->_logger()->info("circuit breaker open");
                throw $e;
            } catch(\Exception $e) {
                if(0 == $this->retries || $r < $this->retries) {
                    $d = $this->delay * (1000 * ($this->backoff ** $this->retries));
                    $this->_logger()->info("exception, delaying by $d us");
                    usleep($d);
                }
            }
        }

        $r--;
        $this->_logger()->info("too many retries", ['retries' => $r]);
        throw new TooManyRetriesException("$r retries for $name");
    }
}
