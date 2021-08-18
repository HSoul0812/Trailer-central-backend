<?php

namespace App\Services\Dms\Customer;

interface CustomerServiceInterface
{
    public function importCSV(array $csvData, int $lineNumber, int $dealerId, int $dealerLocationId, int $popularType, string $category);
}
