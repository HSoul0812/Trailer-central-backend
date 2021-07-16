<?php

namespace App\Repositories\CRM\Email;

use App\Models\CRM\Email\BlastSent;
use App\Repositories\Repository;

interface BlastRepositoryInterface extends Repository {
    /**
     * Mark Blast as Sent
     * 
     * @param int $blastId
     * @param int $leadId
     * @param null|string $messageId = null
     * @throws \Exception
     * @return BlastSent
     */
    public function sent(int $blastId, int $leadId, ?string $messageId = null): BlastSent;

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
     * Was Blast Already Sent to Email Address?
     * 
     * @param int $blastId
     * @param string $email
     * @return bool
     */
    public function wasSent(int $blastId, string $email): bool;
}