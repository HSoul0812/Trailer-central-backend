<?php

declare(strict_types=1);

namespace App\Exceptions\Ecommerce;

use App\Services\Ecommerce\Payment\RefundResultInterface;

class AfterRemoteRefundException extends RefundException
{
    /** @var array */
    private $context = [];

    /** @var RefundResultInterface */
    private $result;

    public function withContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function withResult(RefundResultInterface $result): self
    {
        $this->result = $result;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getResult(): RefundResultInterface
    {
        return $this->result;
    }
}
