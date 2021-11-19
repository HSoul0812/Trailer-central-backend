<?php

declare(strict_types=1);

namespace App\Exceptions\Ecommerce;

class AfterRemoteRefundException extends RefundException
{
    /** @var array */
    private $context = [];

    /**  @var int */
    private $textrailId;

    public function withContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function withTextrailId(int $id): self
    {
        $this->textrailId = $id;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getTextrailId(): ?int
    {
        return $this->textrailId;
    }
}
