<?php

namespace Rho;

class NullLogger {
    public function __call($name, $args) {
        // drop it on the floor
    }
}
