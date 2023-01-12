<?php

namespace App\Exceptions\ElasticSearch;

use Throwable;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BadRequestException extends HttpException
{
    private const CONTENT_PARSE_EXCEPTION = 'x_content_parse_exception';

    private const PARSING_EXCEPTION = 'parsing_exception';

    /** @var Response */
    protected $response;

    public function __construct(ResponseInterface $response, Throwable $previous = null)
    {
        parent::__construct($response->getStatusCode(), $response->getBody()->getContents(), $previous);
        //so subsequent reads of body won't be empty
        $response->getBody()->rewind();

        $this->response = $response;
    }

    public function response(): Response
    {
        return $this->response;
    }

    /**
     * @return bool
     */
    public function isParseException(): bool
    {
        $error = json_decode($this->getMessage(), true)['error'];
        if (isset($error['root_cause']) && count($error['root_cause'])) {
            return in_array($error['root_cause'][0]['type'], [self::CONTENT_PARSE_EXCEPTION, self::PARSING_EXCEPTION]);
        }
        return false;
    }

    /**
     * @throws BadRequestException
     */
    public function throwAsServerError()
    {
        throw new self($this->response->withStatus(500, $this->getMessage()));
    }
}
