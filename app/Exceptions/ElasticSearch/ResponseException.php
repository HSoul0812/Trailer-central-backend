<?php

namespace App\Exceptions\ElasticSearch;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use \Throwable;

class ResponseException extends HttpException
{
    /** @var Response */
    protected $response;

    public function __construct(ResponseInterface $response, Throwable $previous = null)
    {
        parent::__construct($response->getStatusCode(), $response->getBody()->getContents(), $previous);
    }

    public function response(): Response
    {
        return $this->response;
    }
}
