<?php

namespace App\Services\CRM\Leads;

use Illuminate\Support\Facades\Mail;
use App\Exceptions\CRM\Leads\SendInquiryFailedException;
use App\Mail\InquiryEmail;
use App\Models\CRM\Leads\Lead;
use App\Models\Parts\Part;
use App\Models\Showroom\Showroom;
use App\Models\Website\Website;
use App\Services\CRM\Leads\InquiryLead;
use App\Services\CRM\Leads\InquiryEmailServiceInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;
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
     * @param WebsiteConfigRepositoryInterface $websiteConfig
     */
    public function __construct(WebsiteConfigRepositoryInterface $websiteConfig) {
        $this->websiteConfig = $websiteConfig;
    }

    /**
     * Send Email for Lead
     * 
     * @param int $leadId
     * @param array $params
     * @throws SendInquiryFailedException
     */
    public function send($leadId, $params) {
        // Get Lead
        $lead = Lead::findOrFail($leadId);

        // Set Params
        $inquiry = $this->getInquiryFromLead($lead);

        // Try/Send Email!
        try {
            // Send Interaction Email
            Mail::to($this->getCleanTo([
                'email' => $inquiry->getToEmail(),
                'name' => $inquiry->getToName()
            ]))->send(new InquiryEmail($inquiry));
        } catch(\Exception $ex) {
            throw new SendInquiryFailedException($ex->getMessage());
        }

        // Returns Params With Attachments
        return $params;
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
        $params['url'] = $params['website_domain'] . $params['referral'];

        // Get Inquiry From Details For Website
        $config = $this->websiteConfig->getValueOrDefault($params['website_id'], 'general/item_email_from');
        $params['logo'] = $config['logo'];
        $params['logo_url'] = $config['logoUrl'];
        $params['from_name'] = $config['fromName'];

        // Get Data By Inquiry Type
        $vars = $this->getInquiryTypeVars($params);

        // Create Inquiry Lead
        return InquiryLead::getViaCC($vars);
    }


    /**
     * Get Inquiry Type Specific Vars
     * 
     * @param array $params
     * @return array
     */
    private function getInquiryTypeVars(array $params): array {
        // Toggle Inquiry Type
        switch($params['inquiry_type']) {
            case "inventory":
            case "bestprice":
                $inventory = Inventory::find($params['item_id']);
                $params['stock'] = !empty($inventory->stock) ? $inventory->stock : $params['stock'];
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
