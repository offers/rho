<?php

namespace Rho\Transport;

use Rho;
use Rho\NullLogger;

abstract class AbstractTransport {
    use Rho\HasLogger;

    protected $server;

    public function __construct($server, $opts = []) {
        $this->opts = $opts;
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
