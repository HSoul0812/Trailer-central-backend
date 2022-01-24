<?php

namespace App\Exceptions\Ecommerce;

/**
 * @property null|string $gatewayErrorCode the JSON deserialized body
 */
class RefundPaymentGatewayException extends RefundHttpClientException
{
    /** @var null|string */
    protected $gatewayErrorCode;

    /**
     * Creates a new API error exception.
     *
     * @param string $message the exception message
     * @param null|int $httpStatus the HTTP status code
     * @param null|string $httpBody the HTTP body as a string
     * @param null|array $jsonBody the JSON deserialized body
     * @param null|array $httpHeaders the HTTP headers array
     * @param null|string $gatewayErrorCode the Stripe error code
     *
     * @return RefundPaymentGatewayException
     */
    public static function factory(
        string  $message,
        ?int    $httpStatus = null,
        ?string $httpBody = null,
        ?array  $jsonBody = null,
        ?array  $httpHeaders = null,
        ?string $gatewayErrorCode = null
    ): RefundPaymentGatewayException
    {
        $instance = parent::factory($message, $httpStatus, $httpBody,  $jsonBody, $httpHeaders);
        $instance->gatewayErrorCode = $gatewayErrorCode;

        return $instance;
    }
}
