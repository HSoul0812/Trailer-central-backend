<?php

namespace App\Services\CRM\Leads\Import;

use App\Models\CRM\Leads\Lead;
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
     * @param User $dealer
     * @param Crawler $adf
     * @throws InvalidAdfImportVendorException
     * @return ADFLead
     */
    public function parseAdf(User $dealer, Crawler $adf) : ADFLead;

    /**
     * Import ADF as Lead
     * 
     * @param ADFLead $lead
     * @return Lead
     */
    public function importLead(ADFLead $lead) : Lead;
}
