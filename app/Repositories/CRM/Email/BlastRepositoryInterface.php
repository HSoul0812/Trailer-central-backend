<?php

namespace App\Repositories\CRM\Email;

use App\Models\CRM\Email\BlastSent;
use App\Repositories\Repository;

interface BlastRepositoryInterface extends Repository {
    /**
     * Mark Blast as Sent
     * 
     * @param array $params
     * @return BlastSent
     */
    public function sent(array $params): BlastSent;

    /**
     * Was Blast Already Sent?
     * 
     * @param int $blastId
     * @param int $leadId
     * @return bool
     */
    public function wasSent(int $blastId, int $leadId): bool;
}