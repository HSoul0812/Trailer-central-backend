<?php

namespace App\Services\CRM\Leads\Import;

use App\Exceptions\CRM\Leads\Import\InvalidImportFormatException;
use App\Models\User\User;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Illuminate\Support\Facades\Log;

/**
 * Class HtmlService
 * @package App\Services\CRM\Leads\Import
 */
class HtmlService implements ImportTypeInterface
{
    const LEAD_DESTINATION_BLOCK = 'LEAD DESTINATION';
    const INDIVIDUAL_PROSPECT_BLOCK = 'INDIVIDUAL PROSPECT';
    const LEAD_INFORMATION_BLOCK = 'LEAD INFORMATION';
    const SALES_BOAT_BLOCK = 'SALES BOAT';
    const OFFICE_INFO_BLOCK = 'OFFICEINFO';
    const CUSTOMER_COMMENTS_BLOCK = 'CUSTOMER COMMENTS';

    const LEAD_BLOCKS = [
        self::LEAD_DESTINATION_BLOCK,
        self::INDIVIDUAL_PROSPECT_BLOCK,
        self::LEAD_INFORMATION_BLOCK,
        self::SALES_BOAT_BLOCK,
        self::OFFICE_INFO_BLOCK,
        self::CUSTOMER_COMMENTS_BLOCK,
    ];

    /**
     * @param User $dealer
     * @param ParsedEmail $parsedEmail
     * @return ADFLead
     * @throws InvalidImportFormatException
     */
    public function getLead(User $dealer, ParsedEmail $parsedEmail): ADFLead
    {
        if (!$this->isSatisfiedBy($parsedEmail)) {
            Log::error("Body text failed to parse HTML correctly:\r\n\r\n" . $parsedEmail->getBody());
            throw new InvalidImportFormatException;
        }

        $lead = $this->parseHtml($dealer, $parsedEmail->getBody());
        Log::info('Parsed ADF Lead ' . $lead->getFullName() . ' For Dealer ID #' . $lead->getDealerId());

        return $lead;
    }

    /**
     * @param ParsedEmail $parsedEmail
     * @return bool
     */
    public function isSatisfiedBy(ParsedEmail $parsedEmail): bool
    {
        foreach (self::LEAD_BLOCKS as $leadBlock) {
            if (strpos($parsedEmail->getBody(), $leadBlock) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param User $dealer
     * @param string $html
     * @return ADFLead
     */
    private function parseHtml(User $dealer, string $html): ADFLead
    {
        $html = strip_tags($html);

        print_r($html);exit();

        $lead = new ADFLead();

        $lead->setDealerId($dealer->dealer_id);
        $lead->setWebsiteId($dealer->website->id);


    }
}
