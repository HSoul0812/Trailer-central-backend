<?php

declare(strict_types=1);

namespace App\Services\Ecommerce\Payment;

use App\Contracts\Support\DTO;

interface RefundResultInterface extends DTO
{
    public function getId(): string;

    public function getStatus(): string;

    public function getReceiptNumber(): string;

    public function getMetaData(): array;
}
