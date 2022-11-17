<?php

namespace App\Repositories\CRM\Email;

use App\Models\CRM\Email\Blast;
use App\Models\CRM\Email\BlastSent;
use App\Repositories\Repository;
use App\Repositories\TransactionalRepository;

interface BlastRepositoryInterface extends Repository, TransactionalRepository
{
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
     * @param int $emailHistoryId
     * @return BlastSent
     * @throws \Exception
     */
    public function updateSent(int $blastId, int $leadId, string $messageId, int $emailHistoryId): BlastSent;

    /**
     * Was Blast Already Sent to Email Address?
     *
     * @param int $blastId
     * @param string $email
     * @return bool
     */
    public function wasSent(int $blastId, string $email): bool;

    /**
     * Get Blast Sent Entry for Lead
     *
     * @param int $blastId
     * @param int $leadId
     * @return null|BlastSent
     */
    public function getSent(int $blastId, int $leadId): ?BlastSent;

    /**
     * Was Blast Already Sent to Lead?
     *
     * @param int $blastId
     * @param int $leadId
     * @return bool
     */
    public function wasLeadSent(int $blastId, int $leadId): bool;

    /**
     * Update Blast
     *
     * @param $params
     * @return Blast
     */
    public function update($params): Blast;
}
