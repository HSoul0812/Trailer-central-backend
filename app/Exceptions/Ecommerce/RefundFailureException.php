<?php

declare(strict_types=1);

namespace App\Exceptions\Ecommerce;

class RefundFailureException extends \RuntimeException
{
    /** @var array */
    private $context = [];

    /**  @var int */
    private $textrailRma;

    public function withContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function withTextrailRma(?int $rma): self
    {
        $this->textrailRma = $rma;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function getTextrailRma(): ?int
    {
        return $this->textrailRma;
    }
}
