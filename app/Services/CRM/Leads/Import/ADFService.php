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

class ADFService implements ImportTypeInterface, ImportSourceInterface
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

        // Create Log
        $this->log = Log::channel('import');
    }

    /**
     * Takes a lead and export it to the IDS system in XML format
     *
     * @param User $dealer
     * @param ParsedEmail $parsedEmail
     * @return ADFLead
     * @throws InvalidImportFormatException
     */
    public function getLead(User $dealer, ParsedEmail $parsedEmail): ADFLead
    {
        $crawler = $this->validateAdf($parsedEmail->getBody());

        $adf = $this->parseAdf($dealer, $crawler);
        $this->log->info('Parsed ADF Lead ' . $adf->getFullName() . ' For Dealer ID #' . $adf->getDealerId());

        return $adf;
    }

    /**
     * @param ParsedEmail $parsedEmail
     * @return bool
     */
    public function isSatisfiedBy(ParsedEmail $parsedEmail): bool
    {
        try {
            $fixed = $this->fixCdata($parsedEmail->getBody());
            $crawler = new Crawler($fixed);
            $adf = $crawler->filter('adf')->first();

            $success = ($adf->count() >= 1 && !empty($adf->nodeName()) && $adf->nodeName() === 'adf');
            if(!$success) {
                $this->log->error('Invalid ADF format detected: ' . $adf->count() . ' adf count, ' .
                        $adf->nodeName() . ' node, is ' .
                        (($adf->nodeName() !== 'adf') ? 'not' : '') . ' adf node');
            }
            return $success;
        } catch (\Exception $e) {
            $this->log->error('Exception occurred trying to confirm ADF is valid format: ' . $e->getMessage());
            return false;
        }
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
            $this->log->error("Body text failed to parse ADF correctly:\r\n\r\n" . $body);
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
        $this->setAdfVendor($adfLead, $adf->filter('vendor'));

        // Get Date
        $adfLead->setRequestDate($adf->filter('requestdate')->text());
        $adfLead->setDealerId($dealer->dealer_id);
        $adfLead->setWebsiteId($dealer->website->id ?? 0);

        // Get Vendor Location
        $this->setAdfVendorLocation($adfLead, $adf->filter('vendor'));

        // Set Contact Details
        $this->setAdfContact($adfLead, $adf->filter('customer'));

        // Set Vehicle Details
        $this->setAdfVehicle($adfLead, $adf->filter('vehicle'));

        // Get ADF Lead
        return $adfLead;
    }


    /**
     * Set ADF Contact Details to ADF Lead
     *
     * @param ADFLead $adfLead
     * @param Crawler $contact
     * @return ADFLead
     */
    private function setAdfContact(ADFLead $adfLead, Crawler $contact): ADFLead {
        // Set First Name
        $adfLead->setFirstName($contact->filterXPath('//contact/name[@part="first"]')->text(''));
        $adfLead->setLastName($contact->filterXPath('//contact/name[@part="last"]')->text(''));

        // Set Contact Details
        $adfLead->setEmail($contact->filterXPath('//contact/email')->text(''));
        $adfLead->setPhone($contact->filterXPath('//contact/phone')->text(''));

        // Set Address Details
        $adfLead->setAddrStreet($contact->filterXPath('//address/street')->text(''));
        $adfLead->setAddrCity($contact->filterXPath('//address/city')->text(''));
        $adfLead->setAddrState($contact->filterXPath('//address/regioncode')->text(''));
        $adfLead->setAddrZip($contact->filterXPath('//address/postalcode')->text(''));

        // Set Comments
        $adfLead->setComments($contact->filter('comments')->text(''));

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
    private function setAdfVehicle(ADFLead $adfLead, Crawler $vehicle): ADFLead {
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
    private function setAdfVendor(ADFLead $adfLead, Crawler $vendor): ADFLead {
        // Get Vendor ID's
        $vendorIds = $vendor->filter('id');
        $vendorIdMap = [];
        foreach($vendorIds as $vendorId) {
            $source = $vendorId->getAttribute('source');
            $vendorIdMap[$source] = $vendorId->textContent;
        }
        $adfLead->setVendorIds($vendorIdMap);

        // Parse Vendor Details
        $adfLead->setVendorName($vendor->filter('vendorname')->text(''));

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
    private function setAdfVendorLocation(ADFLead $adfLead, Crawler $vendor): ADFLead {
        // Set Vendor Address Details
        $adfLead->setVendorAddrStreet($vendor->filterXPath('//contact/address/street')->text(''));
        $adfLead->setVendorAddrCity($vendor->filterXPath('//contact/address/city')->text(''));
        $adfLead->setVendorAddrState($vendor->filterXPath('//contact/address/regioncode')->text(''));
        $adfLead->setVendorAddrZip($vendor->filterXPath('//contact/address/postalcode')->text(''));
        $adfLead->setVendorAddrCountry($vendor->filterXPath('//contact/address/country')->text(''));

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

    /**
     * Given that the ADF format is unique, this returns
     * the class itself as the source which is implementing
     * both, type and source interfaces.
     *
     * @param ParsedEmail $parsedEmail
     * @return ImportSourceInterface|null
     */
    public function findSource(ParsedEmail $parsedEmail): ?ImportSourceInterface
    {
        return $this->isSatisfiedBy($parsedEmail) ? $this : null;
    }
}
