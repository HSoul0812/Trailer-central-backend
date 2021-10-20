<?php

declare(strict_types=1);

namespace App\Exceptions\Ecommerce;

class AfterRemoteRefundException extends RefundException
{
    /** @var array */
    private $context = [];

    public function withContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
