<?php

namespace Rho\Transport;

use Rho;
use Rho\TransportException;
use Rho\{ErrorResponse,Response};
use GuzzleHttp;

class HttpJsonTransport extends AbstractTransport {
    public function __construct($server) {
        $this->client = new GuzzleHttp\Client();
        parent::__construct($server);
    }

    public function rpc($endpoint, array $args = [], array $opts = []): Rho\AbstractResponse {
        // $endpoint is like ['GET', '/example']
        $method = $endpoint[0];
        $url = $this->getServer() . $endpoint[1];
        $opts['query'] = $args;

        try {
            $resp = $this->client->request($method, $url, $opts);
        } catch(GuzzleHttp\Exception\TransferException $e) {
            throw new TransportException($e);
        }
            
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
