<?php

namespace Rho\CircuitBreaker;

trait RedisConstructor {
    protected $redis;
    protected $prefix;

    public function __construct($circuit, $opts = []) {
        if(!isset($opts['redis'])) {
            throw new \InvalidArgumentException('$opts["redis"] must be set');
        }

        if(!isset($opts['name']) || "" == $opts['name']) {
            throw new \InvalidArgumentException('$opts["name"] must be set to a non-empty string');
        }

        $this->redis = $opts['redis'];
        $this->name = $opts['name'];
        $this->prefix = 'cb:' . $this->name . ':';

        parent::__construct($circuit, $opts);
    }
}
