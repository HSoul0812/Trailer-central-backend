<?php

namespace App\Repositories\CRM\Email;

use App\Repositories\Repository;
use Illuminate\Support\Collection;

interface BounceRepositoryInterface extends Repository {
    /**
     * Was Email Address Bounced/Complained/Unsubscribed?
     * 
     * @param string $email
     * @return null|string
     */
    public function wasBounced(string $email): ?string;

    /**
     * Get all bounces with malformed emails
     *
     * @return Collection
     */
    public function getAllMalformed(): Collection;

    /**
     * Get all bounces with malformed emails
     *
     * @param string $email
     * @return Collection
     */
    public function parseEmail(string $email): string;
}