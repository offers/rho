<?php

namespace Rho;

use Rho\BlackHole;

trait HasLogger {
    protected $logger = null;

    protected function _logger() {
        if(null != $this->logger) {
            return $this->logger;
        }

        if(isset($this->opts['logger'])) {
            // clone the logger to modify it w/o affecting the original
            $this->logger = clone $this->opts['logger'];
            // add the Rho classname as an extra param to all records
            $this->logger->pushProcessor(function ($record) {
                $record['extra']['class'] = get_class();
                return $record;
            });
        } else {
            $this->logger = new BlackHole();
        }

        return $this->logger;
    }
}
