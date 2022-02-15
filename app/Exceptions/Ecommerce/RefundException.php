<?php

namespace App\Exceptions\Ecommerce;

class RefundException extends \DomainException
{
    protected $key;

    public function __construct(string $message = "", ?string $key = null, $code = 422, $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->key = $key;
    }

    public function getKey(): ?string
    {
        return $this->key ?? 'refund';
    }

    public function getErrors(): array
    {
        return [
            $this->getKey() => $this->getMessage()
        ];
    }
}
