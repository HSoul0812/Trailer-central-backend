<?php

namespace App\Repositories\CRM\Interactions;

use App\Repositories\Repository;
use Illuminate\Database\Eloquent\Collection;

interface InteractionsRepositoryInterface extends Repository {

    /**
     * Retrieves the list of tasks by dealer id
     * 
     * @param integer $dealerId
     * @return Collection
     */
    public function getTasksByDealerId($dealerId);
    
    /**
     * Returns list of available sort fields
     * 
     * @return array
     */
    public function getTasksSortFields();
    
    /**
     * Returns first 10 interactions (text logs, emails, drafts)
     * 
     * @param array $params
     * @return Collection
     */
    public function getFirst10(array $params) : Collection;

    /**
     * Batch Update Records
     * 
     * @param array $data
     * @param array $where
     * @return int
     */
    public function batchUpdate(array $data, array $where);
    
}
