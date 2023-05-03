<?php

declare(strict_types=1);

namespace App\Services\Leads;

use App\DTOs\Lead\TcApiResponseLead;

interface LeadServiceInterface
{
    /**
     * @param array
     */
    public function create(array $params): TcApiResponseLead;
}
