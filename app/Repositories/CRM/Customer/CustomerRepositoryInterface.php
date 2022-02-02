<?php

namespace App\Repositories\CRM\Customer;

use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\Customer;
use App\Repositories\Repository;

interface CustomerRepositoryInterface extends Repository {

    public function getCustomersWihOpenBalance($dealerId, $perPage = 15);

    public function createFromLead(Lead $lead, $useExisting = true);

    public function getByEmailOrPhone(array $params): ?Customer;

    /**
     * @param array $params
     * @return bool
     */
    public function bulkUpdate(array $params): bool;
}
