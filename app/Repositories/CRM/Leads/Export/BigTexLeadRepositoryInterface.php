<?php

namespace App\Repositories\CRM\Leads\Export;

use App\Repositories\Repository;

interface BigTexLeadRepositoryInterface extends Repository {
    
    /**
     * Gets all leads that haven't been exported to BigTex
     * 
     * @param int $chunkSize chunk size ot use
     * @param callable $callback callable used to process the chunked data retrieved from the DB
     * @param string $fromDate date to start pulling from
     */
    public function getAllNotExportedChunked($callback, string $fromDate, int $chunkSize = 500) : void;
    
}
