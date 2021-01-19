<?php

namespace App\Repositories\CRM\Leads;

use App\Repositories\Repository;
use Illuminate\Support\Collection;

interface ImportRepositoryInterface extends Repository {
    /**
     * Get All Active Lead Import Emails
     * 
     * @return Collection<LeadImport>
     */
    public function getAllActive() : Collection;

    /**
     * From Email Exists in Lead Import Table?
     * 
     * @param string $email
     * @return bool
     */
    public function hasEmail($email) : bool;
}