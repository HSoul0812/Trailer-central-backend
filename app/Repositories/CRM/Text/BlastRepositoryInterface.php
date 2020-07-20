<?php

namespace App\Repositories\CRM\Text;

use App\Repositories\Repository;

interface BlastRepositoryInterface extends Repository {
    /**
     * Get Leads for Blast
     * 
     * @param array $params
     * @return Collection
     */
    public function getLeads($params);

    /**
     * Mark Blast as Sent
     * 
     * @param array $params
     * return BlastSent
     */
    public function sent($params);
}