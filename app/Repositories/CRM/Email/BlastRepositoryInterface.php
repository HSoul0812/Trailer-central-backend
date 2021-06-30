<?php

namespace App\Repositories\CRM\Email;

use App\Models\CRM\Email\BlastSent;
use App\Repositories\Repository;

interface BlastRepositoryInterface extends Repository {
    /**
     * Mark Blast as Sent
     * 
     * @param array $params
     * @throws \Exception
     * @return BlastSent
     */
    public function sent(array $params): BlastSent;

    /**
     * Update Sent Blast
     * 
     * @param int $blastId
     * @param int $leadId
     * @param string $messageId
     * @throws \Exception
     * @return BlastSent
     */
    public function updateSent(int $blastId, int $leadId, string $messageId): BlastSent;

    /**
     * Was Blast Already Sent?
     * 
     * @param int $blastId
     * @param int $leadId
     * @return bool
     */
    public function wasSent(int $blastId, int $leadId): bool;
}