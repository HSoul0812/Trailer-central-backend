<?php

namespace App\Services\CRM\Leads\Import;

use App\Models\CRM\Leads\Lead;
use App\Models\User\User;
use App\Services\Integration\Common\DTOs\ParsedEmail;

/**
 * Class HtmlService
 * @package App\Services\CRM\Leads\Import
 */
class HtmlService implements ImportTypeInterface
{
    public function import(User $dealer, ParsedEmail $parsedEmail): Lead
    {
        // TODO: Implement import() method.
    }

    public function isSatisfiedBy(ParsedEmail $parsedEmail): bool
    {
        // TODO: Implement isSatisfiedBy() method.
    }
}
