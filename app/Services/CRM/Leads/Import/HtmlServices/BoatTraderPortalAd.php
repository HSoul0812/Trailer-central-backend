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

class BoatTraderPortalAd implements ImportSourceInterface
{
    const NEW_SALES_LEAD_BLOCK = 'NEW SALES LEAD';
    const ADDITIONAL_LEAD_DETAILS_BLOCK = 'ADDITIONAL LEAD DETAILS';
    const PROSPECT_DETAILS_BLOCK = 'PROSPECT DETAILS';
    const SALES_BOAT_BLOCK = 'SALES BOAT';
    const LEAD_DESTINATION_BLOCK = 'LEAD DESTINATION';

    const NECESSARY_LEAD_BLOCKS = [
        self::NEW_SALES_LEAD_BLOCK,
        self::ADDITIONAL_LEAD_DETAILS_BLOCK,
        self::PROSPECT_DETAILS_BLOCK,
        self::SALES_BOAT_BLOCK,
        self::LEAD_DESTINATION_BLOCK
    ];

    const LEAD_BLOCKS = [
        self::NEW_SALES_LEAD_BLOCK => [
            'FROM ',
            'Name',
            'Telephone',
            'Email',
            'Customer Comments'
        ],
        self::ADDITIONAL_LEAD_DETAILS_BLOCK => [
            'Lead date',
            'Lead source',
            'Website link',
            'Lead request type'
        ],
        self::PROSPECT_DETAILS_BLOCK => [
            'Name',
            'Telephone',
            'Email'
        ],
        self::SALES_BOAT_BLOCK => [
            'Sale class',
            'Make',
            'Model description',
            'Year',
            'HIN'
        ],
        self::LEAD_DESTINATION_BLOCK => [
            'Address',
            'Email'
        ]
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
        InventoryRepositoryInterface      $inventoryRepository,
        SanitizeHelper                    $sanitizeHelper
    ) {
        $this->locationRepository = $locationRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->sanitizeHelper = $sanitizeHelper;

        // Create Log
        $this->log = Log::channel('import');
    }

    /**
     * @param ParsedEmail $parsedEmail
     * @return bool
     */
    public function isSatisfiedBy(ParsedEmail $parsedEmail): bool
    {
        foreach (self::NECESSARY_LEAD_BLOCKS as $necessaryLeadBlock) {
            if (strpos($parsedEmail->getBody(), $necessaryLeadBlock) === false) {
                return false;
            }
        }

        return true;
    }

    /**
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

        $fullName = explode(' ', $data[self::PROSPECT_DETAILS_BLOCK]['Name']);
        $lead->setFirstName($fullName[0] ?? '');
        $lead->setLastName($fullName[1] ?? '');

        $lead->setEmail($data[self::PROSPECT_DETAILS_BLOCK]['Email'] ?? '');
        $lead->setPhone($data[self::PROSPECT_DETAILS_BLOCK]['Telephone'] ?? '');
        $lead->setComments($data[self::NEW_SALES_LEAD_BLOCK]['Customer Comments'] ?? '');

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

            /*if ($leadBlockName === self::NEW_SALES_LEAD_BLOCK) {
                $data[$leadBlockName] = $this->sanitizeHelper->removeBrokenCharacters(trim(trim($block ?? '', ':')));
            }*/

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
        $explode = $data[self::LEAD_DESTINATION_BLOCK]['Address'];
        $adfLead->setVendorAddrStreet($explode[0] ?? '');
        $adfLead->setVendorAddrCity($explode[1] ?? '');
        $adfLead->setVendorAddrState($explode[2] ?? '');
        $adfLead->setVendorAddrZip($explode[3] ?? '');

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
        $adfLead->setVehicleStock($data[self::SALES_BOAT_BLOCK]['HIN'] ?? '');

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
