<?php

namespace App\Repositories\CRM\Leads;

use App\Repositories\Repository;

interface InquiryRepositoryInterface extends Repository {
    /**
     * Merge or Create Lead
     * 
     * @param array $params
     * @return Lead
     */
    public function mergeOrCreate($params);

    /**
     * Find Matching Lead
     * 
     * @return Lead
     */
    public function findMatch($params);

    /**
     * Find Existing Lead That Matches Current Lead!
     * 
     * @param array $params
     * @return Collection of Lead 
     */
    public function findAllMatches($params);
}