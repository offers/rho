<?php

namespace Rho\Transport;

use Rho;

abstract class AbstractTransport {
    public function __construct($server) {
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
