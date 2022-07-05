<?php

namespace App\Services\CRM\Leads\Import;

use App\Models\CRM\Leads\Lead;
use App\Models\User\User;
use App\Services\Integration\Common\DTOs\ParsedEmail;

/**
 * Class AbstractImportService
 * @package App\Services\CRM\Leads\Import
 */
interface ImportTypeInterface
{
    /**
     * @param ParsedEmail $parsedEmail
     * @return bool
     */
    public function isSatisfiedBy(ParsedEmail $parsedEmail): bool;

    /**
     * @param User $dealer
     * @param ParsedEmail $parsedEmail
     * @return Lead
     */
    public function import(User $dealer, ParsedEmail $parsedEmail): Lead;
}
