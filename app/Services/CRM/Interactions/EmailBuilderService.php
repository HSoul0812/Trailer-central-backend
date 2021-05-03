<?php

namespace App\Services\CRM\Interactions;

use App\Exceptions\CRM\Leads\SendInquiryFailedException;
use App\Mail\InquiryEmail;
use App\Services\CRM\Leads\DTOs\InquiryLead;
use App\Models\CRM\Leads\Lead;
use App\Services\CRM\Email\EmailBuilderServiceInterface;
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
 * Class EmailBuilderService
 * 
 * @package App\Services\CRM\Email
 */
class EmailBuilderService implements EmailBuilderServiceInterface
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
        $this->log = Log::channel('emailbuilder');
    }

    /**
     * Send Lead Emails for Blast
     * 
     * @param array $params
     * @throws SendBlastEmailsFailedException
     * @return bool
     */
    public function sendBlast(array $params): bool {
        // Get Blast Details
        $blast = $this->blasts->get(['id' => $params['id']]);

        // Create Email Builder Email!
        $builder = new EmailBuilderConfig([
            'subject' => $blast->campaign_subject,
            'template' => $blast->template->html,
            'from_email' => $blast->from_email_address
        ]);

        // Try/Send Email!
        try {
            // Loop Leads
            foreach($params['leads'] as $leadId) {
                $this->send($builder, $leadId);

                // Send Notice
                $this->log->info('Sent Email Blast #' . $blast->id . ' to Lead with ID: ' . $leadId);
            }
        } catch(\Exception $ex) {
            $this->log->error($ex->getMessage() . ': ' . $ex->getTraceAsString());
            throw new SendBlastEmailsFailedException($ex->getMessage());
        }

        // Returns True on Success
        $this->log->info('Sent ' . count($params['leads']) . ' Email Blasts for Dealer ' . $params['dealer_id']);
        return true;
    }
}
