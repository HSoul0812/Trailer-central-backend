<?php

namespace App\Services\CRM\Leads\Import;

use App\Exceptions\CRM\Leads\Import\InvalidDealerIdException;
use App\Exceptions\CRM\Leads\Import\InvalidImportFormatException;
use App\Exceptions\CRM\Leads\Import\MissingEmailAccessTokenException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\User\SalesPerson;
use App\Models\Integration\Auth\AccessToken;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\System\EmailRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\CRM\Email\DTOs\ImapConfig;
use App\Services\CRM\Email\ImapService;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use App\Services\Integration\Google\GmailServiceInterface;
use App\Services\Integration\Google\GoogleServiceInterface;
use Carbon\CarbonImmutable;
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
    }

    public function import(): int
    {
        $accessToken = $this->getAccessToken();
        $inbox = config('adf.imports.gmail.inbox');
        $messages = $this->gmail->messages($accessToken, $inbox);

        $total = 0;
        foreach($messages as $mailId) {
            /** @var ParsedEmail $email */
            $email = $this->gmail->message($mailId);

            try {
                $neededService = null;

                foreach ($this->services as $service) {
                    if ($service->isSatisfiedBy($email)) {
                        $neededService = $service;
                        break;
                    }
                }

                if (!$neededService instanceof ImportTypeInterface) {
                    throw new InvalidImportFormatException();
                }

                $dealerId = str_replace('@' . config('adf.imports.gmail.domain'), '', $email->getToEmail());
                try {
                    $dealer = $this->dealers->get(['dealer_id' => $dealerId]);
                } catch (\Exception $e) {
                    throw new InvalidDealerIdException;
                }

                $adfLead = $neededService->getLead($dealer, $email);

                $result = $this->importLead($adfLead);

                if (!empty($result)) {
                    Log::info('Imported ADF Lead ' . $result->identifier . ' and Moved to Processed');
                    $this->gmail->move($accessToken, $mailId, [config('adf.imports.gmail.processed')], [$inbox]);
                    $total++;
                }

            } catch(InvalidDealerIdException $e) {
                if(!empty($dealerId) && is_numeric($dealerId)) {
                    $this->gmail->move($accessToken, $mailId, [config('adf.imports.gmail.unmapped')], [$inbox]);
                } else {
                    $this->gmail->move($accessToken, $mailId, [config('adf.imports.gmail.invalid')], [$inbox]);
                }
                Log::error("Exception returned on Import Message #{$mailId} {$e->getMessage()}: {$e->getTraceAsString()}");
            } catch(InvalidImportFormatException $e) {
                $this->gmail->move($accessToken, $mailId, [config('adf.imports.gmail.invalid')], [$inbox]);
                Log::error("Exception returned on Import Message #{$mailId} {$e->getMessage()}: {$e->getTraceAsString()}");
            } catch(\Exception $e) {
                Log::error("Exception returned on Import Message #{$mailId} {$e->getMessage()}: {$e->getTraceAsString()}");
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
        if(empty($systemEmail->googleToken)) {
            throw new MissingEmailAccessTokenException;
        }

        // Refresh Token
        $accessToken = $systemEmail->googleToken;
        $validate = $this->google->validate($accessToken);
        if(!empty($validate->newToken)) {
            // Refresh Access Token
            $time = CarbonImmutable::now();
            $accessToken = $this->tokens->update([
                'id' => $accessToken->id,
                'access_token' => $validate->newToken['access_token'],
                'id_token' => $validate->newToken['id_token'],
                'expires_in' => $validate->newToken['expires_in'],
                'expires_at' => $time->addSeconds($validate->newToken['expires_in'])->toDateTimeString(),
                'issued_at' => $time->toDateTimeString()
            ]);
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
    private function importLead(ADFLead $adfLead): Lead {
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
}
