<?php

namespace App\Services\CRM\Leads\Import;

use App\Services\CRM\Leads\DTOs\ADFLead;
use Illuminate\Support\Collection;

/**
 * @author David A Conway Jr.
 */
interface ADFServiceInterface {
    /**
     * Takes a lead and export it to the IDS system in XML format
     * 
     * @return int total number of imported adf leads
     */
    public function import() : int;

    /**
     * Get ADF and Return Result
     * 
     * @param int $dealerId
     * @param Crawler $adf
     * @throws InvalidAdfImportFormatException
     * @return ADFLead
     */
    public function parseAdf(int $dealerId, Crawler $adf) : ADFLead;
}
