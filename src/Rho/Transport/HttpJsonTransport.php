<?php

namespace Rho\Transport;

use Rho;
use Rho\TransportException;
use Rho\{ErrorResponse,Response};
use GuzzleHttp;

class HttpJsonTransport extends AbstractTransport {
    public function __construct($server, $opts = []) {
        $this->client = new GuzzleHttp\Client();
        parent::__construct($server, $opts);
    }

    public function rpc($endpoint, array $args = [], array $opts = []): Rho\AbstractResponse {
        // $endpoint is like ['GET', '/example']
        $method = $endpoint[0];
        $url = $this->getServer() . $endpoint[1];
        $opts['query'] = $args;

        $this->_logger()->info("http request", ['method' => $method, 'url' => $url, 'opts' => $opts]);

        try {
            $resp = $this->client->request($method, $url, $opts);
        } catch(GuzzleHttp\Exception\TransferException $e) {
            $this->_logger()->error("GuzzleHttp\Exception\TransferException", ['e' => $e]);
            throw new TransportException($e);
        }
            
        $this->_logger()->info("http response", ['code' => $resp->getStatusCode()]);

        if(200 == $resp->getStatusCode()) {
            $result = @json_decode($resp->getBody(), true);
            if(null == $result) {
                return new Rho\ErrorResponse("json decode failed", $resp);
            }
            return new Response($result, $resp);
        } else {
            return new ErrorResponse($resp->getStatusCode(), $resp);
        }
    }
}
