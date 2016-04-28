<?php

namespace Rho;

class TransportException extends \Exception {
    public function __construct($e) {
        $this->underlyingException = $e;
    }

    public function getUnderylingException() {
        return $this->underlyingException;
    }
}
