<?php

namespace App\Services\CRM\Email;

use App\Exceptions\CRM\Leads\SendInquiryFailedException;
use App\Mail\InquiryEmail;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Models\CRM\Leads\Lead;
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

        // Get Logger
        $this->log = Log::channel('inquiry');
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
        $this->log->info('Inquiry Email Sent to ' . $inquiry->getInquiryTo() .
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
        $params['website_domain'] = !empty($website->domain) ? 'https://' . $website->domain : '';

        // Get Inquiry From Details For Website
        $config = $this->websiteConfig->getValueOrDefault($params['website_id'], 'general/item_email_from');
        $params['logo'] = $config['logo'];
        $params['logo_url'] = $config['logoUrl'];
        $params['from_name'] = $config['fromName'];

        // GetInquiry Stock/Url/Title from the Inventory ID
        if(!empty($params['inventory'][0])) {
            $inventory = $this->inventory->get(['id' => $params['inventory'][0]]);
            $params['stock'] = $inventory->stock;
            $params['url'] = $inventory ? $inventory->getUrl() : '';
            $params['title'] = $inventory->title;
        }

        // Get Inquiry Name/Email
        $details = $this->getInquiryDetails($params);

        // Get Overrides
        $overrides = $this->getInquiryOverrides($details);

        // Get Data By Inquiry Type
        $vars = $this->getInquiryTypeVars($overrides);

        // Create Inquiry Lead
        return new InquiryLead($vars);
    }

    /**
     * {@inheritDoc}
     */
    public function createFromLead(Lead $lead) : InquiryLead
    {
        $params = [];
        $params['dealer_id'] = $lead->dealer_id;
        $params['website_id'] = $lead->website_id;
        $params['dealer_location_id'] = $lead->dealer_location_id;
        $params['inquiry_type'] = $lead->inquiryType;
        $params['lead_types'] = $lead->leadTypes;
        $params['inventory'] = $lead->inventoryIds;
        $params['item_id'] = $lead->inventory_id;
        $params['title'] = $lead->title;
        $params['url'] = $lead->inventory ? $lead->inventory->getUrl() : '';
        $params['referral'] = $lead->referral;
        $params['first_name'] = $lead->first_name;
        $params['last_name'] = $lead->last_name;
        $params['email_address'] = $lead->email_address;
        $params['phone_number'] = $lead->phone_number;
        $params['preferred_contact'] = $lead->preferred_contact;
        $params['address'] = $lead->address;
        $params['city'] = $lead->city;
        $params['state'] = $lead->state;
        $params['zip'] = $lead->zip;
        $params['comments'] = $lead->comments;
        $params['note'] = $lead->note;
        $params['metadata'] = $lead->metadata;
        $params['date_submitted'] = $lead->date_submitted;
        $params['contact_email_sent'] = $lead->contact_email_sent;
        $params['adf_email_sent'] = $lead->adf_email_sent;
        $params['cdk_email_sent'] = $lead->cdk_email_sent;
        $params['is_spam'] = $lead->is_spam;
        $params['lead_source'] = $lead->getSource();
        $params['lead_status'] = $lead->leadStatus ? $lead->leadStatus->status : null;
        return $this->fill($params);
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
            if(!empty($dealerLocation->name) && !empty($dealerLocation->email)) {
                $params['inquiry_name'] = $dealerLocation->name;
                $params['inquiry_email'] = $dealerLocation->email;
                return $params;
            }
        }

        // Get Inquiry Details From Inventory Item?
        if(!empty($params['item_id']) && !in_array($params['inquiry_type'], InquiryLead::NON_INVENTORY_TYPES)) {
            $inventory = $this->inventory->get(['id' => $params['item_id']]);
            if(!empty($inventory->dealerLocation->name) && !empty($inventory->dealerLocation->email)) {
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
     * Get Inquiry Overrides
     *
     * @param array $params
     * @return array
     */
    private function getInquiryOverrides(array $params) {
        // Get Lead Type
        $leadType = reset($params['lead_types']);

        // Get Inquiry From Details For Website
        $toEmails = $this->websiteConfig->getValueOfConfig($params['website_id'], 'contact/email/' . $leadType);
        if(empty($toEmails->value)) {
            $toEmails = $this->websiteConfig->getValueOfConfig($params['website_id'], 'contact/email');
        }

        // Return Original, No Updates
        if(empty($toEmails->value)) {
            return $params;
        }

        // Return Inquiry Email Override
        $params['inquiry_email'] = preg_split('/,|;|\s/', $toEmails->value, null, PREG_SPLIT_NO_EMPTY);
        $this->log->info('Parsed inquiry email overrides to send to: ' . print_r($params['inquiry_email'], true));
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
        // Skip if no Item ID!
        if(empty($params['item_id'])) {
            return $params;
        }

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
