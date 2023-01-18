<?php

namespace App\Services\CRM\Leads\Import;

use App\Models\User\User;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\Integration\Common\DTOs\ParsedEmail;

interface ImportSourceInterface
{
    /**
     * @param ParsedEmail $parsedEmail
     * @return bool
     */
    public function isSatisfiedBy(ParsedEmail $parsedEmail): bool;

    /**
     * @param User $dealer
     * @param ParsedEmail $parsedEmail
     * @return ADFLead
     */
    public function getLead(User $dealer, ParsedEmail $parsedEmail): ADFLead;
}
