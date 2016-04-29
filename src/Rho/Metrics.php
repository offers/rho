<?php

namespace Rho;

class Metrics {
    use HasLogger;
    use HasCollector;

    const STATS = ['time', 'count', 'countFailures', 'countSuccesses'];

    protected $stats = self::STATS;

    public static function wrap($obj, $opts = []) {
        return new Metrics($obj, $opts);
    }

    public function __construct($obj, $opts = []) {
        $this->obj = $obj;
        $this->opts = $opts;

        if(!isset($opts['collector'])) {
            throw new \InvalidArgumentException("\$opts['collector'] must be set");
        }

        if(isset($opts['name'])) {
            $this->name = $opts['name'];
        } else {
            $this->name = strtolower(get_class());
        }

        if(isset($opts['stats'])) {
            $this->stats = $opts['stats'];
            foreach($this->stats as $stat) {
                if(!in_array(self::STATS, $stat)) {
                    throw new \InvalidArgumentException("invalid stat in \$opts['stats']: $stat");
                }
            }
        }
    }

    public function __call($name, $args) {
        $ex = null;
        $result = null;

        $start = microtime(true);
        try {
            $result = call_user_func_array([$this->obj, $name], $args);
        } catch(\Exception $e) {
            $ex = $e;
        }
        $end = microtime(true);

        try {
            foreach($this->stats as $stat) {
                switch($stat) {
                    case 'time':
                        $this->_collector()->histogram($this->name . ".time", $end - $start);
                        break;
                    case 'count':
                        $this->_collector()->increment($this->name . ".count");
                        break;
                    case 'countFailures':
                        if(null != $ex) {
                            $this->_collector()->increment($this->name . ".failures");
                        }
                        break;
                    case 'countSuccesses':
                        if(null == $ex) {
                            $this->_collector()->increment($this->name . ".successes");
                        }
                        break;
                }
            }
        } catch(\Exception $e) {
            $this->_logger()->error($e->getMessage());
        }

        if(null != $ex) {
            throw $ex;
        }

        return $result;
    }
}
