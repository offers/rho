<?php

namespace Rho;

class Response extends AbstractResponse {
    public function __construct($result, $rawResponse) {
        $this->setResult($result);
        $this->setRawResponse($rawResponse);
        return $this;
    }

    public function getResult() {
        return $this->result;
    }

    public function setResult($result) {
        $this->result = $result;
        return $this;
    }

    public function isError(): bool {
        return false;
    }
}
