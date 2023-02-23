<?php

namespace App\Services\Dealers;

interface DealerServiceInterface
{
    public function listByName(string $name): ?array;
}
