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
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

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
        $inbox = config('adf.imports.gmail.inbox');
        $messages = $this->gmail->messages($accessToken, $inbox);

        // Checking Each Message
        $total = 0;
        foreach($messages as $mailId) {
            // Get Message Overview
            $email = $this->gmail->message($mailId);

            // Find Exceptions
            try {
                // Validate ADF
                $adf = $this->parseAdf($email->getBody());

                // Find Email
                $import = $this->imports->find(['email' => $email->getFromEmail()]);
                if(empty($import->id)) {
                    continue;
                }

                // Process Further
                $result = $this->importLead($adf);
                if(!empty($result->identifier)) {
                    $this->gmail->move($accessToken, $mailId, [config('adf.imports.gmail.processed')], [$inbox]);
                    $total++;
                }
            } catch(\Exception $e) {
                $this->gmail->move($accessToken, $mailId, [config('adf.imports.gmail.invalid')], [$inbox]);
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
        $crawler = new Crawler($body);
        $adf = $crawler->filter('adf')->first();
        var_dump($adf);

        // Valid XML?
        if(empty($adf->nodeName) || (!empty($adf->nodeName) && $adf->nodeName !== 'adf')) {
            throw new InvalidAdfImportFormatException;
        }
        var_dump($adf);

        // Get ADF Lead
        return $this->getAdfLead($adf);
    }

    /**
     * Import ADF as Lead
     * 
     * @param AdfLead $lead
     * @return int 1 = imported, 0 = failed
     */
    public function importLead(AdfLead $lead) : int {
        // Save Lead From ADF Data

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

    /**
     * Get ADF Lead
     * 
     * @param AdfLead $adfLead
     */
    private function getAdfLead($adf) {
        // Get ADF Lead
        $adfLead = new ADFLead();

        // Set ADF Lead
    }
}
