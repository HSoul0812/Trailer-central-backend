<?php

namespace App\Repositories\CRM\Leads;

use App\Repositories\Repository;

interface UnitRepositoryInterface extends Repository {

    public function getUnitIds(int $leadId): array;
}