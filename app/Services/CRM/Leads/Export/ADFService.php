<?php

namespace App\Services\CRM\Leads\Export;

use App\Jobs\CRM\Leads\Export\ADFJob;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\Export\LeadEmail;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Services\CRM\Leads\Export\ADFServiceInterface;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use Illuminate\Foundation\Bus\DispatchesJobs;

class ADFService implements ADFServiceInterface
{
    use DispatchesJobs;
    
    /**     
     * @var App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface 
     */
    protected $leadEmailRepository;
    
    /**     
     * @var App\Repositories\CRM\Inventory\InventoryRepositoryInterface 
     */
    protected $inventoryRepository;
    
    /**     
     * @var App\Repositories\User\UserRepositoryInterface 
     */
    protected $userRepository;
    
    /**     
     * @var App\Repositories\User\DealerLocationRepositoryInterface 
     */
    protected $dealerLocationRepository;
    
    public function __construct(
        LeadEmailRepositoryInterface $leadEmailRepository,
        InventoryRepositoryInterface $inventoryRepository,
        UserRepositoryInterface $userRepository,
        DealerLocationRepositoryInterface $dealerLocationRepository
    ) {
        $this->leadEmailRepository = $leadEmailRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->userRepository = $userRepository;
        $this->dealerLocationRepository = $dealerLocationRepository;
    }

    /**
     * Takes a lead and export it to ADF in XML format
     * 
     * @param InquiryLead $inquiry lead to export to IDS
     * @param Lead $lead lead to export to IDS
     * @return bool
     */
    public function export(InquiryLead $inquiry, Lead $lead) : bool {
        $leadEmail = $this->leadEmailRepository->find($inquiry->dealerId, $inquiry->dealerLocationId);
        if ($leadEmail->export_format !== LeadEmail::EXPORT_FORMAT_ADF) {
            return false;
        }

        $hiddenCopiedEmails = explode(',', config('adf.exports.copied_emails'));

        $adf = $this->getAdfLead($inquiry, $lead->identifier);

        // Dispatch ADF Export Job
        $job = new ADFJob($adf, $lead, $leadEmail->to_emails, $leadEmail->copied_emails, $hiddenCopiedEmails);
        $this->dispatch($job->onQueue('inquiry'));
        
        return true;
    }


    /**
     * Create ADF Lead From InquiryLead
     * 
     * @param InquiryLead $inquiry
     * @param int $leadId
     * @return ADFLead
     */
    private function getAdfLead(InquiryLead $inquiry, int $leadId): ADFLead {
        // Initialize ADF Lead Params
        $params = [
            'leadId' => $leadId,
            'subject' => $inquiry->getSubject(),
            'requestDate' => $inquiry->dateSubmitted,
            'firstName' => $inquiry->firstName,
            'lastName' => $inquiry->lastName,
            'email' => $inquiry->emailAddress,
            'phone' => $inquiry->phoneNumber,
            'comments' => $inquiry->comments,
            'addrStreet' => $inquiry->address,
            'addrCity' => $inquiry->city,
            'addrState' => $inquiry->state,
            'addrZip' => $inquiry->zip,
            'vendorProvider' => $inquiry->fromName
        ];

        // Get Vehicle/Vendor Params
        $params2 = array_merge($params, $this->getAdfVehicle($inquiry->inventory));
        $params3 = array_merge($params2, $this->getAdfVendor($inquiry));

        // Return ADF Lead
        return new ADFLead($params3);
    }

    /**
     * Get ADF Vehicle Params From Inventory
     * 
     * @param array<int> $inventory
     * @return array{vehicleYear: int,
     *               vehicleMake: string,
     *               vehicleModel: string,
     *               vehicleStock: string,
     *               vehicleVin: string}
     */
    private function getAdfVehicle(array $inventory): array {
        // Get Inventory
        $item = $this->inventoryRepository->get(['id' => reset($inventory)]);

        // Initialize ADF Lead Params
        return [
            'vehicleYear' => $item->year ?? 0,
            'vehicleMake' => $item->manufacturer ?? '',
            'vehicleModel' => $item->model ?? '',
            'vehicleStock' => $item->stock ?? '',
            'vehicleVin' => $item->vin ?? ''
        ];
    }

    /**
     * Get ADF Vendor Params From Dealer/DealerLocation
     * 
     * @param InquiryLead $inquiry
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
    private function getAdfVendor(InquiryLead $inquiry): array {
        // Get Dealer & Location
        $dealer = $this->userRepository->get(['dealer_id' => $inquiry->dealerId]);
        $location = $this->dealerLocationRepository->get(['id' => $inquiry->dealerLocationId]);

        // Initialize ADF Lead Params
        return [
            'dealerId' => $inquiry->dealerId,
            'locationId' => $inquiry->dealerLocationId,
            'vendorName' => $dealer->name,
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
