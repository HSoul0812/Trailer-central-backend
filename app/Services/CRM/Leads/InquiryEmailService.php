<?php

namespace App\Services\CRM\Leads;

use App\Exceptions\CRM\Leads\SendInquiryFailedException;
use App\Mail\InquiryEmail;
use App\Models\Inventory\Inventory;
use App\Models\Parts\Part;
use App\Models\Showroom\Showroom;
use App\Models\Website\Website;
use App\Models\User\User;
use App\Models\User\DealerLocation;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Services\CRM\Leads\InquiryEmailServiceInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Class InquiryEmailService
 * 
 * @package App\Services\CRM\Leads
 */
class InquiryEmailService implements InquiryEmailServiceInterface
{
    use CustomerHelper, MailHelper;

    /**
     * @var App\Repositories\Website\Config\WebsiteConfigRepositoryInterface
     */
    protected $websiteConfig;

    /**
     * @var Illuminate\Support\Facades\Log
     */
    protected $log;

    /**
     * @param WebsiteConfigRepositoryInterface $websiteConfig
     */
    public function __construct(WebsiteConfigRepositoryInterface $websiteConfig) {
        $this->websiteConfig = $websiteConfig;

        // Initialize Logger
        $this->log = Log::channel('leads');
    }

    /**
     * Send Email for Lead
     * 
     * @param LeadInquiry $inquiry
     * @throws SendInquiryFailedException
     * @return bool
     */
    public function send(InquiryLead $inquiry): bool {
        // Try/Send Email!
        try {
            // Initialize Interaction Email
            $email = Mail::to($this->getCleanTo($inquiry->getInquiryTo()));

            // Append BCC
            if(!empty($this->getInquiryBcc())) {
                $email->bcc($this->getCleanTo($inquiry->getInquiryBcc()));
            }

            // Send Interaction Email
            $email->send(new InquiryEmail($inquiry));
        } catch(\Exception $ex) {
            $this->log->error($ex->getMessage() . ': ' . $ex->getTraceAsString());
            throw new SendInquiryFailedException($ex->getMessage());
        }

        // Returns True on Success
        $this->log->info('Inquiry Email Sent to ' . $inquiry->inquiryEmail .
                            ' for the Lead ' . $inquiry->getFullName());
        return true;
    }

    /**
     * Fill Inquiry Lead Details From Request Params
     * 
     * @param array $params
     * @return InquiryLead
     */
    public function fill(array $params): InquiryLead {
        // Get Website
        $website = Website::find($params['website_id']);
        $params['website_domain'] = $website->domain;

        // Get Inquiry From Details For Website
        $config = $this->websiteConfig->getValueOrDefault($params['website_id'], 'general/item_email_from');
        $params['logo'] = $config['logo'];
        $params['logo_url'] = $config['logoUrl'];
        $params['from_name'] = $config['fromName'];

        // Get Inquiry Name/Email
        $details = $this->getInquiryDetails($params);

        // Get Data By Inquiry Type
        $vars = $this->getInquiryTypeVars($details);

        // Create Inquiry Lead
        return InquiryLead::getViaCC($vars);
    }


    /**
     * Get Inquiry Name/Email Details
     * 
     * @param array $params
     * @return array_merge($params, array{'inquiry_email': string,
     *                                    'inquiry_name': string})
     */
    private function getInquiryDetails(array $params): array {
        // Is Dev?
        if(!empty($params['is_dev'])) {
            $params['inquiry_name'] = InquiryLead::INQUIRY_DEV_NAME;
            $params['inquiry_email'] = InquiryLead::INQUIRY_DEV_EMAIL;
            return $params;
        }

        // Get Inquiry Details From Dealer Location?
        if(!empty($params['dealer_location_id'])) {
            $dealerLocation = DealerLocation::find($params['dealer_location_id']);
            if(!empty($dealerLocation->name)) {
                $params['inquiry_name'] = $dealerLocation->name;
                $params['inquiry_email'] = $dealerLocation->email;
                return $params;
            }
        }

        // Get Inquiry Details From Inventory Item?
        if(!empty($params['item_id']) && !in_array($params['inquiry_type'], InquiryLead::NON_INVENTORY_TYPES)) {
            $inventory = Inventory::find($params['item_id']);
            if(!empty($inventory->dealerLocation->name)) {
                $params['inquiry_name'] = $inventory->dealerLocation->name;
                $params['inquiry_email'] = $inventory->dealerLocation->email;
                return $params;
            }
        }

        // Get Inquiry Details From Dealer
        $dealer = User::find($params['dealer_id']);
        $params['inquiry_name'] = $dealer->name;
        $params['inquiry_email'] = $dealer->email;
        return $params;
    }

    /**
     * Get Inquiry Type Specific Vars
     * 
     * @param array $params
     * @return array_merge($params, array{'stock': string,
     *                                    'title': string})
     */
    private function getInquiryTypeVars(array $params): array {
        // Toggle Inquiry Type
        switch($params['inquiry_type']) {
            case "inventory":
            case "bestprice":
                $inventory = Inventory::find($params['item_id']);
                $params['stock'] = !empty($inventory->stock) ? $inventory->stock : '';
                $params['title'] = $inventory->title;
            break;
            case "part":
                $part = Part::find($params['item_id']);
                $params['stock'] = !empty($part->sku) ? $part->sku : $params['stock'];
                $params['title'] = $part->title;
            break;
            case "showroomModel":
                $showroom = Showroom::find($params['item_id']);
                $title = $showroom->year . ' '. $showroom->manufacturer;
                $title .= (!empty($showroom->series) ? ' ' . $showroom->series : '');
                $title .= ' ' . $showroom->model;
                $params['title'] = $title;
            break;
        }

        // Return Updated Params Array
        return $params;
    }
}
