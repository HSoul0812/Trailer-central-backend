<?php

namespace App\Services\CRM\Leads\Import;

use App\Exceptions\CRM\Leads\Import\InvalidDealerIdException;
use App\Exceptions\CRM\Leads\Import\InvalidImportFormatException;
use App\Exceptions\CRM\Leads\Import\MissingEmailAccessTokenException;
use App\Models\CRM\Leads\Lead;
use App\Models\Integration\Auth\AccessToken;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\System\EmailRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Google\GoogleService;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Class ImportService
 * @package App\Services\CRM\Leads\Import
 */
class ImportService implements ImportServiceInterface
{
    /**
     * @var ImportTypeInterface[]
     */
    private $services;

    /**
     * @var EmailRepositoryInterface
     */
    private $emails;

    /**
     * @var GoogleServiceInterface
     */
    private $google;

    /**
     * @var TokenRepositoryInterface
     */
    private $tokens;

    /**
     * @var GmailServiceInterface
     */
    protected $gmail;

    /**
     * @var UserRepositoryInterface
     */
    protected $dealers;

    /**
     * @var LeadServiceInterface
     */
    protected $leadService;

    public function __construct(
        EmailRepositoryInterface $emails,
        GoogleServiceInterface $google,
        TokenRepositoryInterface $tokens,
        UserRepositoryInterface $dealers,
        GmailServiceInterface $gmail,
        LeadServiceInterface $leadService,
        ADFService $adfService,
        HtmlService $htmlService
    ) {
        $this->emails = $emails;
        $this->google = $google;
        $this->tokens = $tokens;
        $this->gmail = $gmail;
        $this->dealers = $dealers;
        $this->leadService = $leadService;

        $this->services = [$adfService, $htmlService];

        // Create Log
        $this->log = Log::channel('import');
    }

    /**
     * Takes a lead and import it to the system in various formats
     *
     * @return int
     * @throws MissingEmailAccessTokenException
     */
    public function import(): int
    {
        // Get Emails From Service
        $accessToken = $this->getAccessToken();
        $address = config('adf.imports.gmail.email');
        $this->log->info('Getting Messages With Access Token ' . print_r($accessToken, true));

        // Get Messages for Access Token
        $messages = $this->gmail->messages($accessToken, config('adf.imports.gmail.inbox'));
        $this->log->info('Parsing ' . count($messages) . ' Email Messages From Email Address ' . $address);

        // Checking Each Message
        $total = 0;
        foreach ($messages as $mailId) {
            /** @var ParsedEmail $email */
            $email = $this->gmail->message($mailId);
            $this->log->info('Parsing Email Message #' . $mailId . ' From Email Address ' . $address);

            // Find Exceptions
            try {
                // Confirm Needed Service
                $importSource = null;
                foreach ($this->services as $service) {
                    if ($matchedSource = $service->findSource($email)) {
                        $importSource = $matchedSource;
                        break;
                    }
                }

                // No Service Found, Throw Exception
                if (!$importSource instanceof ImportSourceInterface) {
                    throw new InvalidImportFormatException();
                }

                // Find Dealer ID
                $dealerId = str_replace('@' . config('adf.imports.gmail.domain'), '', $email->getToEmail());
                try {
                    $dealer = $this->dealers->get(['dealer_id' => $dealerId]);
                    $this->log->info('Parsing Email #' . $mailId . ' Import for Dealer #' . $dealerId);
                } catch (Exception $e) {
                    $this->log->error("Exception occurred Parsing Email #{$mailId} for Dealer #{$dealerId}: {$e->getMessage()}");
                    throw new InvalidDealerIdException;
                }

                // Import Lead
                $adfLead = $importSource->getLead($dealer, $email);

                // Process Further
                $result = $this->importLead($adfLead);
                if (!empty($result)) {
                    $this->log->info('Imported ADF Lead ' . $result->identifier);
                    $this->tryMove($accessToken, $mailId, 'processed');
                    $total++;
                }
            } catch (InvalidDealerIdException $e) {
                if (!empty($dealerId) && is_numeric($dealerId)) {
                    $this->tryMove($accessToken, $mailId, 'unmapped');
                } else {
                    $this->tryMove($accessToken, $mailId, 'invalid');
                }
                $this->log->error("Invalid Dealer Exception on Import Message #{$mailId}: {$e->getMessage()}");
            } catch (InvalidImportFormatException $e) {
                $this->tryMove($accessToken, $mailId, 'invalid');
                $this->log->error("Invalid Import Exception on Message #{$mailId}: {$e->getMessage()}");
            } catch (Exception $e) {
                $this->log->error("Exception returned on ADF Import Message #{$mailId}: " .
                                    "{$e->getMessage()}: {$e->getTraceAsString()}");
            }
        }

        return $total;
    }

    /**
     * Get Access Token for ADF
     *
     * @return AccessToken
     * @throws MissingEmailAccessTokenException
     */
    private function getAccessToken(): AccessToken
    {
        // Get Email
        $email = config('adf.imports.gmail.email');

        // Get System Email With Access Token
        $systemEmail = $this->emails->find(['email' => $email]);

        // No Access Token?
        if (empty($systemEmail->googleToken)) {
            throw new MissingEmailAccessTokenException;
        }

        // Refresh Token
        $accessToken = $systemEmail->googleToken;
        $this->google->setKey(GoogleService::AUTH_TYPE_SYSTEM);
        $validate = $this->google->validate($accessToken);
        if ($validate->newToken && $validate->newToken->exists()) {
            // Refresh Access Token
            $time = CarbonImmutable::now();
            $accessToken = $this->tokens->refresh($accessToken->id, $validate->newToken);
        }

        // Return Access Token for Google
        return $accessToken;
    }

    /**
     * Import ADF as Lead
     *
     * @param ADFLead $adfLead
     * @return Lead
     */
    private function importLead(ADFLead $adfLead): Lead
    {
        // Save Lead From ADF Data
        return $this->leadService->create([
            'website_id' => $adfLead->getWebsiteId(),
            'dealer_id' => $adfLead->getDealerId(),
            'dealer_location_id' => $adfLead->getLocationId(),
            'inventory_id' => $adfLead->getVehicleId(),
            'lead_type' => $adfLead->getLeadType(),
            'referral' => 'adf',
            'title' => 'ADF Import',
            'first_name' => $adfLead->getFirstName(),
            'last_name' => $adfLead->getLastName(),
            'email_address' => $adfLead->getEmail(),
            'phone_number' => $adfLead->getPhone(),
            'preferred_contact' => $adfLead->getPreferredContact(),
            'address' => $adfLead->getAddrStreet(),
            'city' => $adfLead->getAddrCity(),
            'state' => $adfLead->getAddrState(),
            'zip' => $adfLead->getAddrZip(),
            'comments' => $adfLead->getComments(),
            'contact_email_sent' => $adfLead->getRequestDate(),
            'adf_email_sent' => $adfLead->getRequestDate(),
            'cdk_email_sent' => 1,
            'date_submitted' => $adfLead->getRequestDate(),
            'lead_source' => $adfLead->getVendorProvider()
        ]);
    }


    /**
     * Try Moving Email
     *
     * @param AccessToken $accessToken
     * @param string $mailId
     * @param string $add ; label to add from config
     * @return void
     */
    private function tryMove(AccessToken $accessToken, string $mailId, string $add): void
    {
        // Are We Moving Labels Right Now?!
        $move = config('adf.imports.gmail.move', true);

        // Get Inbox Label
        $inbox = config('adf.imports.gmail.inbox');

        // Yes?
        if ($move) {
            $this->gmail->move($accessToken, $mailId, [config('adf.imports.gmail.' . $add)], [$inbox]);
            $this->log->info('Moved ADF Email #' . $mailId . ' to ' . ucfirst($add));
        }
    }
}
