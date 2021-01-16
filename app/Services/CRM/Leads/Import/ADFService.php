<?php

namespace App\Services\CRM\Leads\Import;

use App\Exceptions\CRM\Leads\Import\InvalidAdfImportFormatException;
use App\Repositories\CRM\Leads\LeadImportRepositoryInterface;
use App\Models\Integration\Auth\AccessToken;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;

class ADFService implements ADFImportServiceInterface {
    
    /**     
     * @var App\Repositories\CRM\Leads\LeadImportRepositoryInterface
     */
    protected $imports;
    
    public function __construct(LeadImportRepositoryInterface $imports, GmailServiceInterface $service) {
        $this->imports = $imports;
        $this->service = $service;
    }

    /**
     * Checks Inbox for ADF Leads to Import
     * 
     * @param App\Models\CRM\Leads\Lead $lead lead to export to IDS
     */
    public function import() : int {
        // Get Emails From Service
        $accessToken = $this->getAccessToken();
        $messages = $this->service->messages($accessToken, config('adf.imports.gmail.folder'));

        // Checking Each Message
        $total = 0;
        foreach($messages as $mailId) {
            // Get Message Overview
            $email = $this->imap->overview($mailId);

            // Does From Match?
            if(!$this->imports->hasEmail($email->getFromEmail())) {
                continue;
            }

            // Find Exceptions
            try {
                // Process Further
                $result = $this->importLead($email);
                if(!empty($result->identifier)) {
                    $total++;
                }
            } catch(\Exception $e) {
                Log::error("Exception returned on ADF Import Message #{$mailId} {$e->getMessage()}: {$e->getTraceAsString()}");
            }
        }

        // Return Total
        return $total;
    }

    /**
     * Get ADF and Return Result
     * 
     * @param string $body
     * @return ADFLead
     */
    public function parseAdf(string $body) : ADFLead {
        // Get XML Parsed Data
        $parser = \xml_parser_create();
        $valid = \xml_parse($parser, $body, true);
        \xml_parser_free($parser);

        // Valid XML?
        if(empty($valid)) {
            throw new InvalidAdfImportFormatException;
        }

        // Get ADF Lead
        return $this->getAdfLead($xml);
    }


    /**
     * Import ADF as Lead
     * 
     * @param ParsedEmail $email
     * @return int 1 = imported, 0 = failed
     */
    private function importLead(ParsedEmail $email) : int {
        // Get ADF Data
        $adf = $this->parseAdf($email->getBody());

        // Return Total
        return 1;
    }

    /**
     * Get Access Token for ADF
     * 
     * @return AccessToken
     */
    private function getAccessToken() : AccessToken {
        // Initialize Access Token
        $accessToken = new AccessToken();

        // Get Expires
        $issuedAt = config('adf.imports.gmail.issued_at');
        $expiresIn = (int) config('adf.imports.gmail.expires_in');
        $carbon = CarbonImmutable::parse($issuedAt);

        // Insert Access Token
        $accessToken->fill([
            'access_token' => config('adf.imports.gmail.access_token'),
            'refresh_token' => config('adf.imports.gmail.refresh_token'),
            'id_token' => config('adf.imports.gmail.id_token'),
            'expires_in' => $expiresIn,
            'expires_at' => $carbon->addSeconds($expiresIn)->toDateTimeString(),
            'issued_at' => $issuedAt
        ]);

        // Return Access Token
        return $accessToken;
    }
}
