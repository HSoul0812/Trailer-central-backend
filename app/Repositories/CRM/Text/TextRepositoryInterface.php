<?php

namespace App\Repositories\CRM\Text;

use App\Repositories\Repository;

interface TextRepositoryInterface extends Repository {
    /**
     * Send Text
     * 
     * @param type $params
     * @return type
     */
    public function send($params);
}