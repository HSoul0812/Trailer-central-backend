<?php

namespace App\Services\CRM\Leads\Import;

use App\Exceptions\CRM\Leads\Import\InvalidImportFormatException;
use App\Helpers\SanitizeHelper;
use App\Models\User\User;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
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
        self::LEAD_DESTINATION_BLOCK => [
            'Address'
        ],
        self::INDIVIDUAL_PROSPECT_BLOCK => [
            'Name',
            'Telephone',
            'Email',
        ],
        self::LEAD_INFORMATION_BLOCK => [
            'Lead date',
            'Lead source',
            'Lead request type',
        ],
        self::SALES_BOAT_BLOCK => [
            'Sale class',
            'Make',
            'Model description',
            'Year',
            'IMT ID',
            'URI',
        ],
        self::OFFICE_INFO_BLOCK => [
            'Name',
            'City',
            'State/Province',
            'Zip/Postal code'
        ],
        self::CUSTOMER_COMMENTS_BLOCK => [],
    ];

    /**
     * @var DealerLocationRepositoryInterface
     */
    protected $locationRepository;

    /**
     * @var InventoryRepositoryInterface
     */
    protected $inventoryRepository;

    /**
     * @var SanitizeHelper
     */
    protected $sanitizeHelper;

    /**
     * @param DealerLocationRepositoryInterface $locationRepository
     * @param InventoryRepositoryInterface $inventoryRepository
     * @param SanitizeHelper $sanitizeHelper
     */
    public function __construct(
        DealerLocationRepositoryInterface $locationRepository,
        InventoryRepositoryInterface $inventoryRepository,
        SanitizeHelper $sanitizeHelper
    ) {
        $this->locationRepository = $locationRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->sanitizeHelper = $sanitizeHelper;

        // Create Log
        $this->log = Log::channel('import');
    }

    /**
     * @param User $dealer
     * @param ParsedEmail $parsedEmail
     * @return ADFLead
     * @throws InvalidImportFormatException
     */
    public function getLead(User $dealer, ParsedEmail $parsedEmail): ADFLead
    {
        if (!$this->isSatisfiedBy($parsedEmail)) {
            $this->log->error("Body text failed to parse HTML correctly:\r\n\r\n" . $parsedEmail->getBody());
            throw new InvalidImportFormatException;
        }

        $lead = $this->parseHtml($dealer, $parsedEmail->getBody());
        $this->log->info('Parsed ADF Lead ' . $lead->getFullName() . ' For Dealer ID #' . $lead->getDealerId());

        return $lead;
    }

    /**
     * @param ParsedEmail $parsedEmail
     * @return bool
     */
    public function isSatisfiedBy(ParsedEmail $parsedEmail): bool
    {
        foreach (self::LEAD_BLOCKS as $leadBlockName => $leadBlock) {
            if (strpos($parsedEmail->getBody(), $leadBlockName) === false) {
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

        $lead = new ADFLead();

        $lead->setDealerId($dealer->dealer_id);
        $lead->setWebsiteId($dealer->website->id ?? 0);

        $data = $this->getData($html);

        $lead = $this->getVendorLocation($lead, $data);
        $lead = $this->getLeadVehicle($lead, $data);

        $fullName = explode(' ', $data[self::INDIVIDUAL_PROSPECT_BLOCK]['Name']);
        $lead->setFirstName($fullName[0] ?? '');
        $lead->setLastName($fullName[1] ?? '');

        $lead->setEmail($data[self::INDIVIDUAL_PROSPECT_BLOCK]['Email']);
        $lead->setPhone($data[self::INDIVIDUAL_PROSPECT_BLOCK]['Telephone']);
        $lead->setAddrStreet($data[self::OFFICE_INFO_BLOCK]['Name'] ?? '');
        $lead->setAddrCity($data[self::OFFICE_INFO_BLOCK]['City'] ?? '');
        $lead->setAddrState($data[self::OFFICE_INFO_BLOCK]['State/Province'] ?? '');
        $lead->setAddrZip($data[self::OFFICE_INFO_BLOCK]['Zip/Postal code'] ?? '');
        $lead->setComments($data[self::CUSTOMER_COMMENTS_BLOCK] ?? '');

        return $lead;
    }

    private function getData(string $html): array
    {
        $data = [];

        foreach (self::LEAD_BLOCKS as $leadBlockName => $leadBlock) {
            $block = substr($html, strpos($html, $leadBlockName));
            $blockEndPosition = strlen($block);

            foreach (self::LEAD_BLOCKS as $leadBlockName2 => $leadBlock2) {
                if ($leadBlockName2 === $leadBlockName) {
                    continue;
                }

                $leadBlockPosition = strpos($block, $leadBlockName2);

                if ($leadBlockPosition === false) {
                    continue;
                }

                $blockEndPosition = min($leadBlockPosition, $blockEndPosition);
            }

            $block = str_replace($leadBlockName, '', substr($block, 0, $blockEndPosition));

            if ($leadBlockName === self::CUSTOMER_COMMENTS_BLOCK) {
                $data[$leadBlockName] = $this->sanitizeHelper->removeBrokenCharacters(trim(trim($block ?? '', ':')));
            }

            foreach (self::LEAD_BLOCKS[$leadBlockName] as $leadField) {
                $fieldValue = substr($block, strpos($block, $leadField));
                $fieldEndPosition = strlen($fieldValue);

                foreach (self::LEAD_BLOCKS[$leadBlockName] as $leadField2) {
                    if ($leadField === $leadField2) {
                        continue;
                    }

                    $leadFieldPosition = strpos($fieldValue, $leadField2);

                    if ($leadFieldPosition === false) {
                        continue;
                    }

                    $fieldEndPosition = min($leadFieldPosition, $fieldEndPosition);
                }

                $data[$leadBlockName][$leadField] = substr($fieldValue, 0, $fieldEndPosition);
                $data[$leadBlockName][$leadField] = str_replace($leadField, '', $data[$leadBlockName][$leadField]);
                $data[$leadBlockName][$leadField] = preg_replace('/\s/u', ' ', $data[$leadBlockName][$leadField]);
                $data[$leadBlockName][$leadField] = trim(trim($data[$leadBlockName][$leadField], ':'));
            }
        }

        return $data;
    }

    /**
     * Set ADF Vendor Location Details
     *
     * @param ADFLead $adfLead
     * @param array $data
     * @return ADFLead
     */
    private function getVendorLocation(ADFLead $adfLead, array $data): ADFLead
    {
        $adfLead->setVendorAddrStreet($data[self::OFFICE_INFO_BLOCK]['Name'] ?? '');
        $adfLead->setVendorAddrCity($data[self::OFFICE_INFO_BLOCK]['City'] ?? '');
        $adfLead->setVendorAddrState($data[self::OFFICE_INFO_BLOCK]['State/Province'] ?? '');
        $adfLead->setVendorAddrZip($data[self::OFFICE_INFO_BLOCK]['Zip/Postal code'] ?? '');

        $filters = $adfLead->getVendorAddrFilters();

        if(!empty($filters) && count($filters) > 1) {
            $location = $this->locationRepository->find($filters);

            // Vendor Location Exists as Dealer Location?
            if(!empty($location) && $location->count() > 0) {
                $adfLead->setLocationId($location->first()->dealer_location_id);
            }
        }

        return $adfLead;
    }

    /**
     * Set ADF Contact Details to ADF Lead
     *
     * @param ADFLead $adfLead
     * @param array $data
     * @return ADFLead
     */
    private function getLeadVehicle(ADFLead $adfLead, array $data): ADFLead {
        $adfLead->setVehicleYear($data[self::SALES_BOAT_BLOCK]['Year'] ?? '');
        $adfLead->setVehicleMake($data[self::SALES_BOAT_BLOCK]['Make'] ?? '');
        $adfLead->setVehicleModel($data[self::SALES_BOAT_BLOCK]['Model description'] ?? '');

        // Find Inventory Items From DB That Match
        if(!empty($adfLead->getVehicleFilters())) {
            $inventory = $this->inventoryRepository->getAll([
                'dealer_id' => $adfLead->getDealerId(),
                InventoryRepositoryInterface::CONDITION_AND_WHERE_IN => $adfLead->getVehicleFilters()
            ]);

            if(!empty($inventory) && $inventory->count() > 0) {
                $adfLead->setVehicleId($inventory->first()->inventory_id);
            }
        }

        return $adfLead;
    }
}
