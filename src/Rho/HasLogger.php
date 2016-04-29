<?php

namespace Rho;

trait HasLogger {
    protected $logger = null;

    protected function _logger() {
        if(null != $this->logger) {
            return $this->logger;
        }

        if(isset($this->opts['logger'])) {
            $this->logger = $this->opts['logger']->withName(get_class());
        } else {
            $this->logger = new NullLogger();
        }

        return $this->logger;
    }

}
