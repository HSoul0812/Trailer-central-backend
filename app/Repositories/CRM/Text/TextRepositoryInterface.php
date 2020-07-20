<?php

namespace App\Repositories\CRM\Text;

use App\Repositories\Repository;

interface TextRepositoryInterface extends Repository {
    /**
     * Send Text
     * 
     * @param int $leadId
     * @param string $textMessage
     * @return type
     */
    public function send($leadId, $textMessage);
}