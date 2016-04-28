<?php

namespace Rho\Transport;

use Rho;
use Rho\NullLogger;

abstract class AbstractTransport {
    protected $server;
    protected $logger;

    public function __construct($server, $opts = []) {
        if(isset($opts['logger'])) {
            $this->logger = $opts['logger']->withName('Transport');
        } else {
            $this->logger = new NullLogger();
        }

        $this->setServer($server);
    }
    
    public function getServer() {
        return $this->server;
    }

    public function setServer($server): AbstractTransport {
        $this->server = $server;
        return $this;
    }

    abstract public function rpc($endpoint, array $args, array $opts): Rho\AbstractResponse;
}
