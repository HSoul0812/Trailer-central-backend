<?php

namespace App\Services\CRM\Leads\Import;

use App\Exceptions\CRM\Leads\Import\InvalidImportFormatException;
use App\Models\User\User;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\CRM\Leads\Import\HtmlServices\BoatsCom;
use App\Services\CRM\Leads\Import\HtmlServices\BoatTraderPortalAd;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;

/**
 * Class HtmlService
 * @package App\Services\CRM\Leads\Import
 */
class HtmlService implements ImportTypeInterface
{
    /**
     * @var ImportSourceInterface[]
     */
    public const HTML_SOURCES_NAMES = [
        BoatsCom::class,
        BoatTraderPortalAd::class
    ];

    /**
     * @var ImportSourceInterface[]
     */
    protected $htmlSources = [];

    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        foreach (self::HTML_SOURCES_NAMES as $htmlSourceName) {
            $this->htmlSources[] = app()->make($htmlSourceName);
        }

        $this->log = Log::channel('import');
    }

    /**
     * @param User $dealer
     * @param ParsedEmail $parsedEmail
     * @param ImportSourceInterface|null $htmlSource
     * @return ADFLead
     * @throws InvalidImportFormatException
     */
    public function getLead(User $dealer, ParsedEmail $parsedEmail, ImportSourceInterface $htmlSource = null): ADFLead
    {
        // In case no source was provided
        if (empty($htmlSource)) {
            $htmlSource = self::findSource($parsedEmail);
        }

        // Verify we have a valid source
        if (empty($htmlSource) || !$htmlSource->isSatisfiedBy($parsedEmail)) {
            $this->log->error("Body text failed to parse HTML correctly:\r\n\r\n" . $parsedEmail->getBody());
            throw new InvalidImportFormatException;
        }

        // Get the lead
        $lead = $htmlSource->getLead($dealer, $parsedEmail);
        $this->log->info('Parsed HTML Lead ' . $lead->getFullName() . ' For Dealer ID #' . $lead->getDealerId());

        return $lead;
    }

    /**
     * @param ParsedEmail $parsedEmail
     * @return ImportTypeInterface|null
     */
    public function findSource(ParsedEmail $parsedEmail): ?ImportSourceInterface
    {
        foreach ($this->htmlSources as $htmlSource) {
            if ($htmlSource->isSatisfiedBy($parsedEmail)) {
                return $htmlSource;
            }
        }

        return null;
    }
}
