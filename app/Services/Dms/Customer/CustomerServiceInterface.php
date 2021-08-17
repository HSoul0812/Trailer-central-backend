<?php

namespace App\Services\Dms\Customer;

interface CustomerServiceInterface
{
    public function importCSV(array $csvData, int $lineNumber, int $dealer_id, int $dealer_location_id, int $popular_type, string $category);
}
