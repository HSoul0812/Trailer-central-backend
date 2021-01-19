<?php

namespace App\Services\CRM\Leads\Import;

use App\Exceptions\CRM\Leads\Import\InvalidAdfImportFormatException;
use App\Exceptions\CRM\Leads\Import\MissingAdfEmailAccessTokenException;
use App\Repositories\CRM\Leads\ImportRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\System\EmailRepositoryInterface;
use App\Models\Integration\Auth\AccessToken;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Carbon\CarbonImmutable;

class ADFService implements ADFServiceInterface {

    /**     
     * @var App\Repositories\CRM\Leads\ImportRepositoryInterface
     */
    protected $imports;

    /**     
     * @var App\Repositories\System\EmailRepositoryInterface
     */
    protected $emails;

    /**
     * @var App\Repositories\Integration\Auth\TokenRepositoryInterface
     */
    protected $tokens;

    /**     
     * @var App\Services\Integration\Google\GoogleServiceInterface
     */
    protected $google;

    /**     
     * @var App\Services\Integration\Google\GmailServiceInterface
     */
    protected $gmail;
    
    public function __construct(ImportRepositoryInterface $imports,
                                EmailRepositoryInterface $emails,
                                TokenRepositoryInterface $tokens,
                                GoogleServiceInterface $google,
                                GmailServiceInterface $gmail) {
        $this->imports = $imports;
        $this->emails = $emails;
        $this->tokens = $tokens;
        $this->google = $google;
        $this->gmail = $gmail;
    }

    /**
     * Takes a lead and export it to the IDS system in XML format
     * 
     * @return int total number of imported adf leads
     */
    public function import() : int {
        // Get Emails From Service
        $accessToken = $this->getAccessToken();
        var_dump($accessToken);
        var_dump($accessToken->scope);
        var_dump($accessToken->scopes);
        $messages = $this->gmail->messages($accessToken, config('adf.imports.gmail.folder'));

        // Checking Each Message
        $total = 0;
        foreach($messages as $mailId) {
            // Get Message Overview
            $email = $this->gmail->overview($mailId);

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
        // Get Email
        $email = config('adf.imports.gmail.email');

        // Get System Email With Access Token
        $systemEmail = $this->emails->find(['email' => $email]);

        // No Access Token?
        if(empty($systemEmail->googleToken)) {
            throw new MissingAdfEmailAccessTokenException;
        }

        // Refresh Token
        $accessToken = $systemEmail->googleToken;
        $validate = $this->google->validate($accessToken);
        if(!empty($validate['new_token'])) {
            // Refresh Access Token
            $time = CarbonImmutable::now();
            $accessToken = $this->tokens->update([
                'id' => $accessToken->id,
                'access_token' => $validate['new_token']['access_token'],
                'id_token' => $validate['new_token']['id_token'],
                'expires_in' => $validate['new_token']['expires_in'],
                'expires_at' => $time->addSeconds($validate['new_token']['expires_in'])->toDateTimeString(),
                'issued_at' => $time->toDateTimeString()
            ]);
        }

        // Return Access Token for Google
        return $accessToken;
    }
}
