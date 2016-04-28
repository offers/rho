<?php

namespace Rho;

abstract class AbstractResponse {
    abstract public function isError(): bool;

    public function getRawResponse() {
        return $this->rawResponse;
    }

    public function setRawResponse($raw) {
        $this->rawResponse = $raw;
        return $this;
    }
}
