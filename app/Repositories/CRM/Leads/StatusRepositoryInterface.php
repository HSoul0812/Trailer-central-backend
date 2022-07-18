<?php

namespace App\Repositories\CRM\Leads;

use App\Models\CRM\Leads\LeadStatus;
use App\Repositories\Repository;

interface StatusRepositoryInterface extends Repository {
    /**
     * Create or Update Lead Status
     *
     * @param array $params
     * @return LeadStatus
     */
    public function createOrUpdate(array $params): LeadStatus;

    /**
     * Create Lead Status
     *
     * @param array $params
     * @return LeadStatus
     */
    public function create($params): LeadStatus;

    /**
     * Update Lead Status
     *
     * @param array $params
     * @return LeadStatus
     */
    public function update($params): LeadStatus;

    /**
     * Find Lead Status by id.
     *
     * @param $id
     * @return LeadStatus
     */
    public function find($id): LeadStatus;
}
