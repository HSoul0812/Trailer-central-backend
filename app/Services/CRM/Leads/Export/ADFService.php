<?php

namespace App\Services\CRM\Leads\Export;

use App\Exceptions\PropertyDoesNotExists;
use App\Jobs\CRM\Leads\Export\ADFJob;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\Export\LeadEmail;
use App\Models\Inventory\Inventory;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Services\CRM\Leads\DTOs\ADFLead;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;

class ADFService implements ADFServiceInterface
{
    use DispatchesJobs;

    /**
     * @var App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface
     */
    protected $leadEmailRepository;

    /**
     * @var App\Repositories\User\DealerLocationRepositoryInterface
     */
    protected $dealerLocationRepository;

    /**
     * @var App\Repositories\Website\Config\WebsiteConfigRepositoryInterface
     */
    protected $websiteConfig;

    protected $adfParams;

    public function __construct(
        LeadEmailRepositoryInterface $leadEmailRepository,
        WebsiteConfigRepositoryInterface $websiteConfig,
        DealerLocationRepositoryInterface $dealerLocationRepository
    ) {
        $this->leadEmailRepository = $leadEmailRepository;
        $this->websiteConfig = $websiteConfig;
        $this->dealerLocationRepository = $dealerLocationRepository;
    }

    /**
     * Takes a lead and export it to ADF in XML format
     *
     * @param Lead $lead lead to export to ADF
     * @return bool
     * @throws PropertyDoesNotExists
     * @throws \Exception
     */
    public function export(Lead $lead): bool
    {
        /*
         * If the lead comes with dealer location 0 or null
         * And we have an inventory assigned to the lead
         * Use the inventory dealer location instead
         */
        if (empty($lead->dealer_location_id) && !empty($lead->inventory)) {
            $lead->dealer_location_id = $lead->inventory->dealer_location_id;
        }

        $leadEmail = $this->leadEmailRepository->find($lead->dealer_id, $lead->dealer_location_id);
        if (!$leadEmail) {
            Log::info("Lead {$lead->identifier} couldn't find a LeadEmail associated.");
            return false;
        } elseif ($leadEmail->export_format !== LeadEmail::EXPORT_FORMAT_ADF) {
            Log::info("Lead {$lead->identifier} export format is not ADF.");
            return false;
        }

        $hiddenCopiedEmails = explode(',', config('adf.exports.copied_emails'));

        $adf = $this->getAdfLead($lead);

        // Dispatch ADF Export Job
        $job = new ADFJob($adf, $lead, $leadEmail->to_emails, $leadEmail->copied_emails, $hiddenCopiedEmails);
        $this->dispatch($job->onQueue('inquiry'));
        return true;
    }


    /**
     * Create ADF Lead From InquiryLead
     *
     * @param Lead $lead
     * @return ADFLead
     * @throws PropertyDoesNotExists
     */
    private function getAdfLead(Lead $lead): ADFLead
    {
        $config = $this->websiteConfig->getValueOrDefault($lead->website_id, 'general/item_email_from');

        // Initialize ADF Lead Params
        $this->adfParams = [
            'leadId' => $lead->identifier,
            'subject' => $lead->lead_type,
            'requestDate' => $lead->date_submitted,
            'firstName' => $lead->first_name,
            'lastName' => $lead->last_name,
            'email' => $lead->email_address,
            'phone' => $lead->phone_number,
            'comments' => $lead->comments,
            'addrStreet' => $lead->address,
            'addrCity' => $lead->city,
            'addrState' => $lead->state,
            'addrZip' => $lead->zip,
            'vendorProvider' => $config['fromName']
        ];

        // Get Vehicle/Vendor Params
        $inventory = $lead->inventory ?? new Inventory();

        $this->adfParams = array_merge($this->adfParams, $this->getAdfVehicle($inventory));
        $this->adfParams = array_merge($this->adfParams, $this->getAdfVendor($lead));

        // Return ADF Lead
        return new ADFLead($this->adfParams);
    }

    /**
     * Get ADF Vehicle Params From Inventory
     *
     * @param Inventory $inventory
     * @return array{vehicleYear: int,
     *               vehicleMake: string,
     *               vehicleModel: string,
     *               vehicleStock: string,
     *               vehicleVin: string}
     */
    private function getAdfVehicle(Inventory $inventory): array
    {
        // Initialize ADF Lead Params
        return [
            'vehicleYear' => !empty($inventory->year) ? $inventory->year : 0,
            'vehicleMake' => !empty($inventory->manufacturer) ? $inventory->manufacturer : '',
            'vehicleModel' => !empty($inventory->model) ? $inventory->model : '',
            'vehicleStock' => !empty($inventory->stock) ? $inventory->stock : '',
            'vehicleVin' => !empty($inventory->vin) ? $inventory->vin : ''
        ];
    }

    /**
     * Get ADF Vendor Params From Dealer/DealerLocation
     *
     * @param Lead $lead
     * @return array{dealerId: int,
     *               locationId: int,
     *               vendorName: string,
     *               vendorContact: string,
     *               vendorUrl: string,
     *               vendorEmail: string,
     *               vendorPhone: string,
     *               vendorAddrStreet: string,
     *               vendorAddrCity: string,
     *               vendorAddrState: string,
     *               vendorAddrZip: string,
     *               vendorAddrCountry: string}
     */
    private function getAdfVendor(Lead $lead): array
    {
        // Get Location
        if ($lead->dealer_location_id) {
            $location = $this->dealerLocationRepository->get(['id' => $lead->dealer_location_id]);
        } else {
            $location = $this->dealerLocationRepository->get(['dealer_id' => $lead->dealer_id]);
        }

        // Initialize ADF Lead Params
        return [
            'dealerId' => $lead->dealer_id,
            'locationId' => $lead->dealer_location_id,
            'vendorName' => $lead->user->name,
            'vendorContact' => $location->contact,
            'vendorUrl' => $location->website,
            'vendorEmail' => $location->email,
            'vendorPhone' => $location->phone,
            'vendorAddrStreet' => $location->address,
            'vendorAddrCity' => $location->city,
            'vendorAddrState' => $location->region,
            'vendorAddrZip' => $location->postalcode,
            'vendorAddrCountry' => $location->country,
        ];
    }
}
