<?php

declare(strict_types=1);

namespace App\Exceptions\Ecommerce;

use App\Traits\WithGetter;

/**
 * @property null|string $httpBody the HTTP body as a string
 * @property null|array $httpHeaders
 * @property null|int $httpStatus the HTTP body as a string
 * @property null|array $jsonBody the HTTP status code
 * @property null|string $gatewayErrorCode the JSON deserialized body
 */
class RefundHttpClientException extends RefundException
{
    use WithGetter;

    /** @var null|string the HTTP body as a string */
    protected $httpBody;

    /** @var null|array */
    protected $httpHeaders;

    /** @var null|int the HTTP status code */
    protected $httpStatus;

    /** @var null|array $jsonBody the JSON deserialized body */
    protected $jsonBody;

    /**
     * Creates a new API error exception.
     *
     * @param string $message the exception message
     * @param null|int $httpStatus the HTTP status code
     * @param null|string $httpBody the HTTP body as a string
     * @param null|array $jsonBody the JSON deserialized body
     * @param null|array $httpHeaders the HTTP headers array
     *
     * @return static
     */
    public static function factory(
        string  $message,
        ?int    $httpStatus = null,
        ?string $httpBody = null,
        ?array  $jsonBody = null,
        ?array  $httpHeaders = null
    ): RefundPaymentGatewayException
    {
        $instance = new static($message);
        $instance->httpBody = $httpBody;
        $instance->httpStatus = $httpStatus;
        $instance->jsonBody = $jsonBody;
        $instance->httpHeaders = $httpHeaders;

        return $instance;
    }

    /**
     * Returns the string representation of the exception.
     */
    public function __toString(): string
    {
        $statusStr = $this->httpStatus !== null ? "(Status {$this->httpStatus}) " : '';

        return "{$statusStr}{$this->getMessage()}";
    }
}
