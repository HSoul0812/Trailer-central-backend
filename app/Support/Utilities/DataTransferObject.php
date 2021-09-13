<?php

declare(strict_types=1);

namespace App\Support\Utilities;

interface DataTransferObject
{
    public function asArray(): array;
}
