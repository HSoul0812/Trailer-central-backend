<?php

declare(strict_types=1);

namespace App\Exceptions\Ecommerce;

use Throwable;

class RefundException extends \DomainException
{
    protected $key;

    public function __construct(string $message = "", ?string $key = null, int $code = 422, ?Throwable $previous = null)
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
