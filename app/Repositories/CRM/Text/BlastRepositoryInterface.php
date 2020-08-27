<?php

namespace App\Repositories\CRM\Text;

use App\Repositories\Repository;

interface BlastRepositoryInterface extends Repository {
    /**
     * Get All Active Blasts For Dealer
     * 
     * @param int $userId
     * @return Collection of Blast
     */
    public function getAllActive($userId);

    /**
     * Mark Blast as Sent
     * 
     * @param array $params
     * return BlastSent
     */
    public function sent($params);
}