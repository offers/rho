<?php

namespace Rho;

class ErrorResponse extends AbstractResponse {
    public function __construct($errorData, $rawResponse) {
        $this->setError($errorData);
        $this->setRawResponse($rawResponse);
        return $this;
    }

    public function setError($errorData): ErrorResponse {
        $this->errorData = $errorData;
        return $this;
    }

    public function getError() {
        return $this->errorData;
    }

    public function isError(): bool {
        return true;
    }
}
