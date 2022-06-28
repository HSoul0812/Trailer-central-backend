<?php

namespace App\Services\CRM\Leads\Import;

use App\Models\CRM\Leads\Lead;
use App\Services\CRM\Leads\DTOs\ADFLead;

/**
 * Class AbstractImportService
 * @package App\Services\CRM\Leads\Import
 */
abstract class AbstractImportService implements ImportServiceInterface
{
    /**
     * Import ADF as Lead
     *
     * @param ADFLead $lead
     * @return Lead
     */
    abstract public function importLead(ADFLead $lead) : Lead;

    /**
     * @param string $body
     * @return bool
     */
    abstract public function isSatisfiedBy(string $body): bool;
}
