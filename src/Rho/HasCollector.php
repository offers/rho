<?php

namespace Rho;

use Rho\BlackHole;

trait HasCollector {
    protected $collector = null;

    protected function _collector() {
        if(null != $this->collector) {
            return $this->collector;
        }

        if(isset($this->opts['collector'])) {
            $this->collector = $this->opts['collector'];
        } else {
            $this->collector = new BlackHole();
        }

        return $this->collector;
    }
}
