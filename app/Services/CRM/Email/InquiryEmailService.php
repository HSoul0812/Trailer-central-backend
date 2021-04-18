<?php

namespace App\Services\CRM\Email;

use App\Exceptions\CRM\Leads\SendInquiryFailedException;
use App\Mail\InquiryEmail;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Services\CRM\Email\InquiryEmailServiceInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Repositories\Website\WebsiteRepositoryInterface;
use App\Repositories\Website\Config\WebsiteConfigRepositoryInterface;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Repositories\Showroom\ShowroomRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Traits\CustomerHelper;
use App\Traits\MailHelper;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Class InquiryEmailService
 * 
 * @package App\Services\CRM\Leads
 */
class InquiryEmailService implements InquiryEmailServiceInterface
{
    use CustomerHelper, MailHelper;

    /**
     * @var App\Repositories\Inventory\InventoryRepositoryInterface
     */
    protected $inventory;

    /**
     * @var App\Repositories\Parts\PartRepositoryInterface
     */
    protected $part;

    /**
     * @var App\Repositories\Showroom\ShowroomRepositoryInterface
     */
    protected $showroom;

    /**
     * @var App\Repositories\Website\WebsiteRepositoryInterface
     */
    protected $website;

    /**
     * @var App\Repositories\Website\Config\WebsiteConfigRepositoryInterface
     */
    protected $websiteConfig;

    /**
     * @var App\Repositories\User\UserRepositoryInterface
     */
    protected $user;

    /**
     * @var App\Repositories\User\DealerLocationRepositoryInterface
     */
    protected $dealerLocation;

    /**
     * @var Illuminate\Support\Facades\Log
     */
    protected $log;

    /**
     * @param InventoryRepositoryInterface $inventory
     * @param PartRepositoryInterface $part
     * @param ShowroomRepositoryInterface $showroom
     * @param WebsiteRepositoryInterface $website
     * @param WebsiteConfigRepositoryInterface $websiteConfig
     * @param UserRepositoryInterface $user
     * @param DealerLocationRepositoryInterface $dealerLocation
     */
    public function __construct(
        InventoryRepositoryInterface $inventory,
        PartRepositoryInterface $part,
        ShowroomRepositoryInterface $showroom,
        WebsiteRepositoryInterface $website,
        WebsiteConfigRepositoryInterface $websiteConfig,
        UserRepositoryInterface $user,
        DealerLocationRepositoryInterface $dealerLocation
    ) {
        $this->inventory = $inventory;
        $this->part = $part;
        $this->showroom = $showroom;
        $this->website = $website;
        $this->websiteConfig = $websiteConfig;
        $this->user = $user;
        $this->dealerLocation = $dealerLocation;

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
            $email = Mail::to($this->getCleanTo($inquiry->getInquiryToArray()));

            // Append BCC
            if(!empty($inquiry->getInquiryBccArray())) {
                $email = $email->bcc($this->getCleanTo($inquiry->getInquiryBccArray()));
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
        $website = $this->website->get(['id' => $params['website_id']]);
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
        return new InquiryLead($vars);
    }


    /**
     * Get Inquiry Name/Email Details
     * 
     * @param array $params
     * @return array_merge($params, array{'inquiry_email': string,
     *                                    'inquiry_name': string})
     */
    private function getInquiryDetails(array $params): array {
        // Get Inquiry Details From Dealer Location?
        if(!empty($params['dealer_location_id'])) {
            $dealerLocation = $this->dealerLocation->get(['id' => $params['dealer_location_id']]);
            if(!empty($dealerLocation->name)) {
                $params['inquiry_name'] = $dealerLocation->name;
                $params['inquiry_email'] = $dealerLocation->email;
                return $params;
            }
        }

        // Get Inquiry Details From Inventory Item?
        if(!empty($params['item_id']) && !in_array($params['inquiry_type'], InquiryLead::NON_INVENTORY_TYPES)) {
            $inventory = $this->inventory->get(['id' => $params['item_id']]);
            if(!empty($inventory->dealerLocation->name)) {
                $params['inquiry_name'] = $inventory->dealerLocation->name;
                $params['inquiry_email'] = $inventory->dealerLocation->email;
                return $params;
            }
        }

        // Get Inquiry Details From Dealer
        $dealer = $this->user->get(['dealer_id' => $params['dealer_id']]);
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
                $inventory = $this->inventory->get(['id' => $params['item_id']]);
                $params['stock'] = !empty($inventory->stock) ? $inventory->stock : '';
                $params['title'] = $inventory->title;
            break;
            case "part":
                $part = $this->part->get(['id' => $params['item_id']]);
                $params['stock'] = !empty($part->sku) ? $part->sku : '';
                $params['title'] = $part->title;
            break;
            case "showroom":
                $showroom = $this->showroom->get(['id' => $params['item_id']]);
                $title = $showroom->year . ' '. $showroom->manufacturer;
                $title .= (!empty($showroom->series) ? ' ' . $showroom->series : '');
                $params['title'] = $title . ' ' . $showroom->model;
            break;
        }

        // Return Updated Params Array
        return $params;
    }
}
