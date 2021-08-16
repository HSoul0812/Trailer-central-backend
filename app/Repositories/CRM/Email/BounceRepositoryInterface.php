<?php

namespace App\Repositories\CRM\Email;

use App\Repositories\Repository;

interface BounceRepositoryInterface extends Repository {
    /**
     * Was Email Address Bounced/Complained/Unsubscribed?
     * 
     * @param string $email
     * @return null|string
     */
    public function wasBounced(string $email): ?string;
}