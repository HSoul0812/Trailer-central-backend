<?php

namespace App\Services\CRM\Leads\Import;

use App\Models\CRM\Leads\LeadImport;
use App\Services\CRM\Leads\DTOs\ADFLead;
use Symfony\Component\DomCrawler\Crawler;

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
     * Validate ADF and Return Result
     * 
     * @param string $body
     * @throws InvalidAdfImportFormatException
     * @return bool
     */
    public function validateAdf(string $body) : Crawler;

    /**
     * Get ADF and Return Result
     * 
     * @param LeadImport $import
     * @param Crawler $adf
     * @throws InvalidAdfImportFormatException
     * @return ADFLead
     */
    public function parseAdf(LeadImport $import, Crawler $adf) : ADFLead;

    /**
     * Import ADF as Lead
     * 
     * @param ADFLead $lead
     * @return int 1 = imported, 0 = failed
     */
    public function importLead(ADFLead $lead) : int;
}
