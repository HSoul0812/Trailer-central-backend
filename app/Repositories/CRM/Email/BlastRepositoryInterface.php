<?php

namespace App\Repositories\CRM\Email;

use App\Repositories\Repository;

interface BlastRepositoryInterface extends Repository {
    /**
     * Mark Blast as Sent
     * 
     * @param array $params
     * return BlastSent
     */
    public function sent($params);
}