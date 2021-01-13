<?php

namespace App\Repositories\CRM\Leads\Export;

use App\Repositories\Repository;

interface IDSLeadRepositoryInterface extends Repository {
    
    /**
     * Gets all leads that haven't been exported to IDS
     * 
     * @param int $chunkSize chunk size ot use
     * @param callable $callback callable used to process the chunked data retrieved from the DB
     */
    public function getAllNotExportedChunked($callback, int $chunkSize = 500) : void;
    
}
