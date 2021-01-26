<?php

namespace App\Repositories\System;

use App\Models\System\Email;
use App\Repositories\Repository;

interface EmailRepositoryInterface extends Repository {
    /**
     * Find System Email
     * 
     * @param array $params
     * @return App\Models\System\Email
     */
    public function find($params): Email;
}