<?php

namespace App\Services\CRM\Leads\Import\HtmlServices;

use App\Exceptions\CRM\Leads\Import\InvalidImportFormatException;
use App\Helpers\SanitizeHelper;
use App\Models\User\User;
use App\Repositories\GenericRepository;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\CRM\Leads\Import\ImportSourceInterface;
use App\Services\Integration\Common\DTOs\ParsedEmail;
use Illuminate\Support\Facades\Log;

class BoatsCom implements ImportSourceInterface
{
    const INDIVIDUAL_PROSPECT_BLOCK = 'INDIVIDUAL PROSPECT';
    const LEAD_INFORMATION_BLOCK = 'LEAD INFORMATION';
    const SALES_BOAT_BLOCK = 'SALES BOAT';
    const OFFICE_INFO_BLOCK = 'OFFICEINFO';
    const CUSTOMER_COMMENTS_BLOCK = 'CUSTOMER COMMENTS';

    const NECESSARY_LEAD_BLOCKS = [
        self::INDIVIDUAL_PROSPECT_BLOCK,
        self::LEAD_INFORMATION_BLOCK,
        self::SALES_BOAT_BLOCK,
        self::OFFICE_INFO_BLOCK,
    ];

    const LEAD_BLOCKS = [
        self::INDIVIDUAL_PROSPECT_BLOCK => [
            'Name',
            'Telephone',
            'Email'
        ],
        self::LEAD_INFORMATION_BLOCK => [
            'Lead date',
            'Lead source',
            'Lead status',
            'Lead request type'
        ],
        self::SALES_BOAT_BLOCK => [
            'Sale class',
            'Make',
            'Model description',
            'Year',
            'HIN',
            'Stock Number',
            'URI'
        ],
        self::OFFICE_INFO_BLOCK => [
            'Sales Contact',
            'Name',
            'Address',
            'Address 2',
            'City',
            'State/Province',
            'Zip/Postal code',
            'Country'
        ],
    ];

    /**
     * @var DealerLocationRepositoryInterface
     */
    private $locationRepository;

    /**
     * @var InventoryRepositoryInterface
     */
    private $inventoryRepository;

    /**
     * @var SanitizeHelper
     */
    private $sanitizeHelper;

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
        foreach (self::NECESSARY_LEAD_BLOCKS as $leadBlockName) {
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

        $lead->setEmail($data[self::INDIVIDUAL_PROSPECT_BLOCK]['Email'] ?? '');
        $lead->setPhone($data[self::INDIVIDUAL_PROSPECT_BLOCK]['Telephone'] ?? '');
        $lead->setAddrStreet($data[self::OFFICE_INFO_BLOCK]['Address'] ?? '');
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
            $leadBlockStartPosition = strpos($html, $leadBlockName);

            if ($leadBlockStartPosition === false) {
                continue;
            }

            $block = substr($html, $leadBlockStartPosition);
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
                $leadFieldStartPosition = strpos($block, $leadField);

                if ($leadFieldStartPosition === false) {
                    continue;
                }

                $fieldValue = substr($block, $leadFieldStartPosition);
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

        if (!empty($filters) && count($filters) > 1) {
            $location = $this->locationRepository->find($filters);

            // Vendor Location Exists as Dealer Location?
            if (!empty($location) && $location->count() > 0) {
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
    private function getLeadVehicle(ADFLead $adfLead, array $data): ADFLead
    {
        $adfLead->setVehicleYear($data[self::SALES_BOAT_BLOCK]['Year'] ?? '');
        $adfLead->setVehicleMake($data[self::SALES_BOAT_BLOCK]['Make'] ?? '');
        $adfLead->setVehicleModel($data[self::SALES_BOAT_BLOCK]['Model description'] ?? '');
        $adfLead->setVehicleStock($data[self::SALES_BOAT_BLOCK]['Stock Number'] ?? '');

        // Find Inventory Items From DB That Match
        if (!empty($adfLead->getVehicleFilters())) {
            $inventory = $this->inventoryRepository->getAll([
                'dealer_id' => $adfLead->getDealerId(),
                GenericRepository::CONDITION_AND_WHERE_IN => $adfLead->getVehicleFilters()
            ]);

            if (!empty($inventory) && $inventory->count() > 0) {
                $adfLead->setVehicleId($inventory->first()->inventory_id);
            }
        }

        return $adfLead;
    }
}
