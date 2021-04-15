<?php

namespace App\Services\CRM\Leads\Export;

use App\Jobs\CRM\Leads\Export\ADFJob;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\Export\LeadEmail;
use App\Models\Inventory\Inventory;
use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface;
use App\Services\CRM\Leads\Export\ADFServiceInterface;
use App\Services\CRM\Leads\DTOs\ADFLead;
use App\Services\CRM\Leads\DTOs\InquiryLead;

class ADFService implements ADFServiceInterface {
    
    /**     
     * @var App\Repositories\CRM\Leads\Export\LeadEmailRepositoryInterface 
     */
    protected $leadEmailRepository;
    
    public function __construct(LeadEmailRepositoryInterface $leadEmailRepository) {
        $this->leadEmailRepository = $leadEmailRepository;
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

        ADFJob::dispatchNow($adf, $lead, $leadEmail->to_emails, $leadEmail->copied_emails, $hiddenCopiedEmails);
        
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
            'leadFirst' => $inquiry->firstName,
            'leadLast' => $inquiry->lastName,
            'leadEmail' => $inquiry->emailAddress,
            'leadPhone' => $inquiry->phoneNumber,
            'leadComments' => $inquiry->comments,
            'leadAddress' => $inquiry->address,
            'leadCity' => $inquiry->city,
            'leadState' => $inquiry->state,
            'leadPostal' => $inquiry->zip,
            'providerName' => $inquiry->fromName
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
        $itemId = reset($inventory);
        $item = Inventory::find($itemId);

        // Initialize ADF Lead Params
        return [
            'vehicleYear' => $item->year ?? 0,
            'vehicleManufacturer' => $item->manufacturer ?? '',
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
     *               dealerLocationId: int,
     *               vendorName: string,
     *               vendorContact: string,
     *               vendorWebsite: string,
     *               vendorEmail: string,
     *               vendorPhone: string,
     *               vendorAddress: string,
     *               vendorCity: string,
     *               vendorState: string,
     *               vendorPostal: string,
     *               vendorCountry: string}
     */
    private function getAdfVendor(InquiryLead $inquiry): array {
        // Get Dealer & Location
        $dealer = User::find($inquiry->dealerId);
        $location = DealerLocation::find($inquiry->dealerLocationId);

        // Initialize ADF Lead Params
        return [
            'dealerId' => $inquiry->dealerId,
            'dealerLocationId' => $inquiry->dealerLocationId,
            'vendorName' => $dealer->name,
            'vendorContact' => $location->contact,
            'vendorWebsite' => $location->website,
            'vendorEmail' => $location->email,
            'vendorPhone' => $location->phone,
            'vendorAddress' => $location->address,
            'vendorCity' => $location->city,
            'vendorState' => $location->region,
            'vendorPostal' => $location->postalcode,
            'vendorCountry' => $location->country,
        ];
    }
}
