<?php

namespace App\Repositories\CRM\Leads;

use App\Models\CRM\Leads\LeadImport;
use App\Repositories\Repository;
use Illuminate\Support\Collection;

interface ImportRepositoryInterface extends Repository {
    /**
     * Get All Active Lead Import Emails
     * 
     * @return Collection<LeadImport>
     */
    public function getAllActive() : Collection;

    /**
     * Find Import Entry in Lead Import Table?
     * 
     * @param array $params
     * @return LeadImport || null
     */
    public function find($params);
}