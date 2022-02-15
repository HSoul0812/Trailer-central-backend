<?php

declare(strict_types=1);

namespace App\Contracts\Support;

interface DTO
{
    public function asArray(): array;
}
