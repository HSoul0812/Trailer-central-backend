<?php

namespace App\Services\CRM\Leads\Import;

use App\Exceptions\CRM\Leads\Import\InvalidImportFormatException;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Models\CRM\Leads\Lead;
use App\Models\User\User;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class ADFService implements ImportTypeInterface
{
    /**
     * @var LeadRepositoryInterface
     */
    protected $leads;

    /**
     * @var InventoryRepositoryInterface
     */
    protected $inventory;

    /**
     * @var DealerLocationRepositoryInterface
     */
    protected $locations;

    public function __construct(
        LeadServiceInterface $leads,
        InventoryRepositoryInterface $inventory,
        DealerLocationRepositoryInterface $locations
    ) {
        $this->leads = $leads;
        $this->inventory = $inventory;
        $this->locations = $locations;
    }

    /**
     * Takes a lead and export it to the IDS system in XML format
     *
     * @param User $dealer
     * @param ParsedEmail $parsedEmail
     * @return Lead
     * @throws InvalidImportFormatException
     */
    public function import(User $dealer, ParsedEmail $parsedEmail): Lead
    {
        $crawler = $this->validateAdf($parsedEmail->getBody());

        $adf = $this->parseAdf($dealer, $crawler);
        Log::info('Parsed ADF Lead ' . $adf->getFullName() . ' For Dealer ID #' . $adf->getDealerId());

        return $this->importLead($adf);
    }

    /**
     * @param ParsedEmail $parsedEmail
     * @return bool
     */
    public function isSatisfiedBy(ParsedEmail $parsedEmail): bool
    {
        $fixed = $this->fixCdata($parsedEmail->getBody());
        $crawler = new Crawler($fixed);
        $adf = $crawler->filter('adf')->first();

        return $adf->count() >= 1 && !empty($adf->nodeName()) && ($adf->nodeName() === 'adf');
    }

    /**
     * Validate ADF and Return Result
     *
     * @param string $body
     * @return Crawler
     * @throws InvalidImportFormatException
     */
    private function validateAdf(string $body): Crawler
{
        // Get XML Parsed Data
        $fixed = $this->fixCdata($body);
        $crawler = new Crawler($fixed);
        $adf = $crawler->filter('adf')->first();

        // Valid XML?
        if($adf->count() < 1 || empty($adf->nodeName()) || ($adf->nodeName() !== 'adf')) {
            Log::error("Body text failed to parse ADF correctly:\r\n\r\n" . $body);
            throw new InvalidImportFormatException;
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
    private function parseAdf(User $dealer, Crawler $adf): ADFLead {
        // Create ADF Lead
        $adfLead = new ADFLead();

        // Set Vendor Provider
        $adfLead->setVendorProvider($adf->filterXPath('//provider/name')->text(ADFLead::DEFAULT_PROVIDER));

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
        $adfLead->setAddrStreet($contact->filterXPath('//address/street')->text(''));
        $adfLead->setAddrCity($contact->filterXPath('//address/city')->text(''));
        $adfLead->setAddrState($contact->filterXPath('//address/regioncode')->text(''));
        $adfLead->setAddrZip($contact->filterXPath('//address/postalcode')->text(''));

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
        $adfLead->setVehicleYear($vehicle->filter('year')->text(''));
        $adfLead->setVehicleMake($vehicle->filter('make')->text(''));
        $adfLead->setVehicleModel($vehicle->filter('model')->text(''));
        $adfLead->setVehicleStock($vehicle->filter('stock')->text(''));
        $adfLead->setVehicleVin($vehicle->filter('vin')->text(''));

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

        // Parse Vendor Details
        $adfLead->setVendorName($vendor->filter('vendorname')->text());

        // Parse Vendor Contact Details
        $adfLead->setVendorContact($vendor->filterXPath('//contact/name')->text(''));
        $adfLead->setVendorUrl($vendor->filterXPath('//contact/url')->text(''));
        $adfLead->setVendorEmail($vendor->filterXPath('//contact/email')->text(''));
        $adfLead->setVendorPhone($vendor->filterXPath('//contact/phone')->text(''));

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
        $adfLead->setVendorAddrStreet($vendor->filterXPath('//address/street')->text(''));
        $adfLead->setVendorAddrCity($vendor->filterXPath('//address/city')->text(''));
        $adfLead->setVendorAddrState($vendor->filterXPath('//address/regioncode')->text(''));
        $adfLead->setVendorAddrZip($vendor->filterXPath('//address/postalcode')->text(''));
        $adfLead->setVendorAddrCountry($vendor->filterXPath('//address/country')->text(''));

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
