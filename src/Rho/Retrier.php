<?php

namespace Rho;

use Rho;
use Rho\Exception\CircuitBreakerOpenException;
use Rho\Exception\TooManyRetriesException;

class Retrier {
    use HasLogger;

    protected $obj;
    protected $retries = 0;
    protected $backoff = 1.0;
    protected $delay = 0.1; // seconds

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
                $this->_logger()->warning("circuit breaker open, won't retry", ['func' => $name]);
                throw $e;
            } catch(\Exception $e) {
                $this->_logger()->error('Exception', ['exception' => $e]);
                if(0 == $this->retries || $r < $this->retries) {
                    $d = $this->delay * 1000000 * ($this->backoff ** $this->retries);
                    $this->_logger()->warning("exception, delaying", ['func' => $name, 'delay' => floatval($d) / 1000000]);
                    usleep($d);
                }
            }
        }

        $r--;
        $this->_logger()->warning("too many retries, giving up", ['func' => $name, 'retries' => $r]);
        throw new TooManyRetriesException("$r retries for $name");
    }
}
