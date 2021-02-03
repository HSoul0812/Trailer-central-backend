<?php

namespace App\Repositories\CRM\Leads;

use App\Models\CRM\Leads\LeadSource;
use App\Repositories\Repository;

interface SourceRepositoryInterface extends Repository {
    /**
     * Create or Update Lead Source
     * 
     * @param array $params
     * @return LeadSource
     */
    public function createOrUpdate($params): LeadSource;

    /**
     * Find Lead Source
     * 
     * @param array $params
     * @return LeadSource|null
     */
    public function find($params): ?LeadSource;
}
