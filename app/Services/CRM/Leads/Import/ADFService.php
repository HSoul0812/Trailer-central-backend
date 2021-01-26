<?php

namespace App\Services\CRM\Leads\Import;

use App\Exceptions\CRM\Leads\Import\InvalidAdfImportFormatException;
use App\Exceptions\CRM\Leads\Import\InvalidAdfDealerIdException;
use App\Exceptions\CRM\Leads\Import\MissingAdfEmailAccessTokenException;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\Integration\Auth\TokenRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\System\EmailRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Models\CRM\Leads\Lead;
use App\Models\Integration\Auth\AccessToken;
use App\Models\User\User;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\Integration\Google\GoogleServiceInterface;
use App\Services\Integration\Google\GmailServiceInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ADFService implements ADFServiceInterface {
    /**
     * @var App\Repositories\CRM\Leads\LeadRepositoryInterface
     */
    protected $leads;

    /**     
     * @var App\Repositories\System\EmailRepositoryInterface
     */
    protected $emails;

    /**
     * @var App\Repositories\Integration\Auth\TokenRepositoryInterface
     */
    protected $tokens;

    /**
     * @var App\Repositories\Inventory\InventoryRepositoryInterface
     */
    protected $inventory;

    /**
     * @var App\Repositories\User\UserRepositoryInterface
     */
    protected $dealers;

    /**
     * @var App\Repositories\User\DealerLocationRepositoryInterface
     */
    protected $locations;

    /**     
     * @var App\Services\Integration\Google\GoogleServiceInterface
     */
    protected $google;

    /**     
     * @var App\Services\Integration\Google\GmailServiceInterface
     */
    protected $gmail;
    
    public function __construct(LeadRepositoryInterface $leads,
                                EmailRepositoryInterface $emails,
                                TokenRepositoryInterface $tokens,
                                InventoryRepositoryInterface $inventory,
                                UserRepositoryInterface $dealers,
                                DealerLocationRepositoryInterface $locations,
                                GoogleServiceInterface $google,
                                GmailServiceInterface $gmail) {
        $this->leads = $leads;
        $this->emails = $emails;
        $this->tokens = $tokens;
        $this->inventory = $inventory;
        $this->dealers = $dealers;
        $this->locations = $locations;
        $this->google = $google;
        $this->gmail = $gmail;
    }

    /**
     * Takes a lead and export it to the IDS system in XML format
     * 
     * @throws InvalidAdfDealerIdException
     * @return int total number of imported adf leads
     */
    public function import(): int {
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
                $crawler = $this->validateAdf($email->getBody());

                // Find Dealer ID
                $dealerId = str_replace('@' . config('adf.imports.gmail.domain'), '', $email->getToEmail());
                try {
                    $dealer = $this->dealers->get(['dealer_id' => $dealerId]);
                } catch(\Exception $e) {
                    throw new InvalidAdfDealerIdException;
                }

                // Validate ADF
                $adf = $this->parseAdf($dealer, $crawler);

                // Process Further
                $result = $this->importLead($adf);
                if(!empty($result->identifier)) {
                    $this->gmail->move($accessToken, $mailId, [config('adf.imports.gmail.processed')], [$inbox]);
                    $total++;
                }
            } catch(InvalidAdfDealerIdException $e) {
                if(!empty($dealerId) && is_numeric($dealerId)) {
                    $this->gmail->move($accessToken, $mailId, [config('adf.imports.gmail.unmapped')], [$inbox]);
                } else {
                    $this->gmail->move($accessToken, $mailId, [config('adf.imports.gmail.invalid')], [$inbox]);
                }
                Log::error("Exception returned on ADF Import Message #{$mailId} {$e->getMessage()}: {$e->getTraceAsString()}");
            } catch(InvalidAdfImportFormatException $e) {
                $this->gmail->move($accessToken, $mailId, [config('adf.imports.gmail.invalid')], [$inbox]);
                Log::error("Exception returned on ADF Import Message #{$mailId} {$e->getMessage()}: {$e->getTraceAsString()}");
            } catch(\Exception $e) {
                Log::error("Exception returned on ADF Import Message #{$mailId} {$e->getMessage()}: {$e->getTraceAsString()}");
            }
        }

        // Return Total
        return $total;
    }

    /**
     * Validate ADF and Return Result
     * 
     * @param string $body
     * @throws InvalidAdfImportFormatException
     * @return Crawler
     */
    public function validateAdf(string $body): Crawler {
        // Get XML Parsed Data
        $fixed = $this->fixCdata($body);
        $crawler = new Crawler($fixed);
        $adf = $crawler->filter('adf')->first();

        // Valid XML?
        if($adf->count() < 1 || empty($adf->nodeName()) || ($adf->nodeName() !== 'adf')) {
            Log::error("Body text failed to parse ADF correctly:\r\n\r\n" . $body);
            throw new InvalidAdfImportFormatException;
        }

        // Return True
        return $adf;
    }

    /**
     * Get ADF and Return Result
     * 
     * @param User $dealer
     * @param Crawler $adf
     * @return ADFLead
     */
    public function parseAdf(User $dealer, Crawler $adf): ADFLead {
        // Create ADF Lead
        $adfLead = new ADFLead();

        // Set Vendor Details
        $this->getAdfVendor($adfLead, $adf->filter('vendor'));

        // Get Date
        $adfLead->setRequestDate($adf->filter('requestdate')->text());
        $adfLead->setDealerId($dealer->dealer_id);
        $adfLead->setWebsiteId($dealer->website->id);

        // Get Vendor Location
        $this->getAdfVendorLocation($adfLead, $adf->filter('vendor'));

        // Set Contact Details
        $this->getAdfContact($adfLead, $adf->filter('customer'));

        // Set Vehicle Details
        $this->getAdfVehicle($adfLead, $adf->filter('vehicle'));

        // Get ADF Lead
        return $adfLead;
    }

    /**
     * Import ADF as Lead
     * 
     * @param ADFLead $adfLead
     * @return Lead
     */
    public function importLead(ADFLead $adfLead): Lead {
        // Save Lead From ADF Data
        return $this->leads->create([
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
     * Get Access Token for ADF
     * 
     * @throws MissingAdfEmailAccessTokenException
     * @return AccessToken
     */
    private function getAccessToken(): AccessToken {
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
     * Set ADF Contact Details to ADF Lead
     * 
     * @param ADFLead $adfLead
     * @param Crawler $contact
     * @return ADFLead
     */
    private function getAdfContact(ADFLead $adfLead, Crawler $contact): ADFLead {
        // Set First Name
        $adfLead->setFirstName($contact->filterXPath('//contact/name[@part="first"]')->text());
        $adfLead->setLastName($contact->filterXPath('//contact/name[@part="last"]')->text());

        // Set Contact Details
        $adfLead->setEmail($contact->filterXPath('//contact/email')->text());
        $adfLead->setPhone($contact->filterXPath('//contact/phone')->text());

        // Set Address Details
        $adfLead->setAddrStreet($contact->filterXPath('//address[@type="home"]/street')->text());
        $adfLead->setAddrCity($contact->filterXPath('//address[@type="home"]/city')->text());
        $adfLead->setAddrState($contact->filterXPath('//address[@type="home"]/regioncode')->text());
        $adfLead->setAddrZip($contact->filterXPath('//address[@type="home"]/postalcode')->text());

        // Set Comments
        $adfLead->setComments($contact->filter('comments')->text());

        // Return ADF Lead
        return $adfLead;
    }

    /**
     * Set ADF Contact Details to ADF Lead
     * 
     * @param ADFLead $adfLead
     * @param Crawler $vehicle
     * @param int $dealerId
     * @return ADFLead
     */
    private function getAdfVehicle(ADFLead $adfLead, Crawler $vehicle): ADFLead {
        // Set Vehicle Details
        $adfLead->setVehicleYear($vehicle->filter('year')->text());
        $adfLead->setVehicleMake($vehicle->filter('make')->text());
        $adfLead->setVehicleModel($vehicle->filter('model')->text());
        $adfLead->setVehicleStock($vehicle->filter('stock')->text());
        $adfLead->setVehicleVin($vehicle->filter('vin')->text());

        // Find Inventory Items From DB That Match
        if(!empty($adfLead->getVehicleFilters())) {
            $inventory = $this->inventory->getAll([
                'dealer_id' => $adfLead->getDealerId(),
                InventoryRepositoryInterface::CONDITION_AND_WHERE_IN => $adfLead->getVehicleFilters()
            ]);

            // Inventory Exists?
            if(!empty($inventory) && $inventory->count() > 0) {
                $adfLead->setVehicleId($inventory->first()->inventory_id);
            }
        }

        // Return ADF Lead
        return $adfLead;
    }

    /**
     * Set ADF Vendor Details
     * 
     * @param ADFLead $adfLead
     * @param Crawler $vendor
     * @return ADFLead
     */
    private function getAdfVendor(ADFLead $adfLead, Crawler $vendor): ADFLead {
        // Get Vendor ID's
        $vendorIds = $vendor->filter('id');
        $vendorIdMap = [];
        foreach($vendorIds as $vendorId) {
            $source = $vendorId->getAttribute('source');
            $vendorIdMap[$source] = $vendorId->textContent;
        }
        $adfLead->setVendorIds($vendorIdMap);

        // Parse Vendor/Provider Details
        $adfLead->setVendorProvider($vendor->filterXPath('//provider/name')->text());
        $adfLead->setVendorName($vendor->filter('vendorname')->text());

        // Parse Vendor Contact Details
        $adfLead->setVendorContact($vendor->filterXPath('//contact/name')->text());
        $adfLead->setVendorUrl($vendor->filterXPath('//contact/url')->text());
        $adfLead->setVendorEmail($vendor->filterXPath('//contact/email')->text());
        $adfLead->setVendorPhone($vendor->filterXPath('//contact/phone')->text());

        // Return ADF Lead
        return $adfLead;
    }

    /**
     * Set ADF Vendor Location Details
     * 
     * @param ADFLead $adfLead
     * @param Crawler $vendor
     * @return ADFLead
     */
    private function getAdfVendorLocation(ADFLead $adfLead, Crawler $vendor): ADFLead {
        // Set Vendor Address Details
        $adfLead->setVendorAddrStreet($vendor->filterXPath('//address[@type="work"]/street')->text());
        $adfLead->setVendorAddrCity($vendor->filterXPath('//address[@type="work"]/city')->text());
        $adfLead->setVendorAddrState($vendor->filterXPath('//address[@type="work"]/regioncode')->text());
        $adfLead->setVendorAddrZip($vendor->filterXPath('//address[@type="work"]/postalcode')->text());
        $adfLead->setVendorAddrCountry($vendor->filterXPath('//address[@type="work"]/country')->text());

        // Get Vendor Location
        $filters = $adfLead->getVendorAddrFilters();
        if(!empty($filters) && count($filters) > 1) {
            $location = $this->locations->find($filters);

            // Vendor Location Exists as Dealer Location?
            if(!empty($location) && $location->count() > 0) {
                $adfLead->setLocationId($location->first()->dealer_location_id);
            }
        }

        // Return ADF Lead
        return $adfLead;
    }


    /**
     * Strip All CData From XML
     * 
     * @param string $xml
     * @return string
     */
    private function fixCdata($xml): string {
        return preg_replace('/<!\[CDATA\[(.*?)\]\]>/', '$1', $xml);
    }
}
