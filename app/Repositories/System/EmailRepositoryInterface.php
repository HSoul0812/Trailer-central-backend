<?php

namespace App\Repositories\System;

use App\Repositories\Repository;

interface EmailRepositoryInterface extends Repository {
    /**
     * Find System Email
     * 
     * @param array $params
     * @return Collection of System Emails
     */
    public function find($params);
}