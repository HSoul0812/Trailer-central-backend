<?php

namespace App\Exceptions\ElasticSearch;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use \Throwable;

class ResponseException extends \RuntimeException
{
    /** @var Response */
    protected $response;

    public function __construct(ResponseInterface $response, Throwable $previous = null)
    {
        $message = 'Elastic search API responded with http code: ' . $response->getStatusCode();

        parent::__construct($message, 500, $previous);
    }

    public function response(): Response
    {
        return $this->response;
    }
}
