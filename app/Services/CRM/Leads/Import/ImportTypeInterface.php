<?php

namespace App\Services\CRM\Leads\Import;

use App\Models\User\User;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\Integration\Common\DTOs\ParsedEmail;

/**
 * Class AbstractImportService
 * @package App\Services\CRM\Leads\Import
 */
interface ImportTypeInterface
{
    /**
     * @param ParsedEmail $parsedEmail
     * @return ImportSourceInterface|null
     */
    public function findSource(ParsedEmail $parsedEmail): ?ImportSourceInterface;

    /**
     * @param User $dealer
     * @param ParsedEmail $parsedEmail
     * @return ADFLead
     */
    public function getLead(User $dealer, ParsedEmail $parsedEmail): ADFLead;
}
