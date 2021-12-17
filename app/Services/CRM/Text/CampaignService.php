<?php

namespace App\Services\CRM\Text;

use App\Exceptions\CRM\Text\CustomerLandlineNumberException;
use App\Exceptions\CRM\Text\NoCampaignSmsFromNumberException;
use App\Exceptions\CRM\Text\NoLeadsProcessCampaignException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Text\CampaignSent;
use App\Services\CRM\Text\TextServiceInterface;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\CRM\Text\CampaignRepositoryInterface;
use App\Repositories\CRM\Text\TemplateRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Class CampaignService
 * 
 * @package App\Services\CRM\Text
 */
class CampaignService implements CampaignServiceInterface
{
    /**
     * @var App\Services\CRM\Text\TextServiceInterface
     */
    protected $textService;

    /**
     * @var App\Repositories\CRM\Leads\LeadRepository
     */
    protected $leads;

    /**
     * @var App\Repositories\CRM\Text\TextRepository
     */
    protected $texts;

    /**
     * @var App\Repositories\CRM\Text\CampaignRepository
     */
    protected $campaigns;

    /**
     * @var App\Repositories\CRM\Text\TemplateRepository
     */
    protected $templates;

    /**
     * @var App\Repositories\User\DealerLocationRepository
     */
    protected $dealerLocation;

    /**
     * @var Log
     */
    private $log;

    /**
     * CampaignService constructor.
     */
    public function __construct(TextServiceInterface $text,
                                StatusRepositoryInterface $leadStatus,
                                TextRepositoryInterface $textRepo,
                                CampaignRepositoryInterface $campaignRepo,
                                TemplateRepositoryInterface $templateRepo,
                                DealerLocationRepositoryInterface $dealerLocationRepo)
    {
        // Initialize Text Service
        $this->textService = $text;

        // Initialize Repositories
        $this->leadStatus = $leadStatus;
        $this->texts = $textRepo;
        $this->campaigns = $campaignRepo;
        $this->templates = $templateRepo;
        $this->dealerLocation = $dealerLocationRepo;

        // Initialize Logger
        $this->log = Log::channel('textcampaign');
    }

    /**
     * Send Campaign Text
     * 
     * @param NewDealerUser $dealer
     * @param Campaign $campaign
     * @throws NoCampaignSmsFromNumberException
     * @throws NoLeadsProcessCampaignException
     * @return Collection<CampaignSent>
     */
    public function send(NewDealerUser $dealer, Campaign $campaign): Collection {
        // Get From Number
        $from_number = $campaign->from_sms_number;
        if(empty($from_number)) {
            $from_number = $this->dealerLocation->findDealerSmsNumber($dealer->id);
            if(empty($from_number)) {
                throw new NoCampaignSmsFromNumberException();
            }
        }

        // Get Unsent Campaign Leads
        if(count($campaign->leads) < 1) {
            throw new NoLeadsProcessCampaignException();
        }

        // Loop Leads for Current Dealer
        $sent = new Collection();
        foreach($campaign->leads as $lead) {
            // Not a Valid To Number?!
            if(empty($lead->text_phone)) {
                continue;
            }

            // Send Lead
            $leadSent = $this->sendToLead($from_number, $dealer, $campaign, $lead);
            $sent->push($leadSent);
        }

        // Return Campaign Sent Entries
        return $sent;
    }

    /**
     * Send Text to Lead
     * 
     * @param string $from_number sms from number
     * @param NewDealerUser $dealer
     * @param Campaign $campaign
     * @param Lead $lead
     * @return CampaignSent
     */
    private function sendToLead($from_number, $dealer, $campaign, $lead) {
        // Get Text Message
        $textMessage = $this->templates->fillTemplate($campaign->template->template, [
            'lead_name' => $lead->full_name,
            'title_of_unit_of_interest' => $lead->inventory_title,
            'dealer_name' => $dealer->user->name
        ]);

        try {
            // Send Text
            $this->textService->send($from_number, $lead->text_phone, $textMessage, $lead->full_name);
            $status = CampaignSent::STATUS_SENT;
        } catch (CustomerLandlineNumberException $ex) {
            $status = CampaignSent::STATUS_LANDLINE;
        } catch (Exception $ex) {
            $status = CampaignSent::STATUS_INVALID;
        }

        // Return Sent Result
        return $this->markLeadSent($from_number, $campaign, $lead, $textMessage, $status);
    }

    /**
     * Mark Lead as Sent
     * 
     * @param string $from_number sms from number
     * @param Campaign $campaign
     * @param Lead $lead
     * @param string $textMessage filled text message
     * @param string $status
     * @return CampaignSent
     */
    private function markLeadSent($from_number, $campaign, $lead, $textMessage, $status) {
        // Handle Transaction
        $textLog = null;
        DB::transaction(function() use ($from_number, $campaign, $lead, $textMessage, &$status, &$textLog) {
            // Save Lead Status
            $this->leadStatus->createOrUpdate([
                'lead_id' => $lead->identifier,
                'status' => Lead::STATUS_MEDIUM,
                'next_contact_date' => Carbon::now()->addDay()->toDateTimeString()
            ]);
            $status = CampaignSent::STATUS_LEAD;

            // Log SMS
            $textLog = $this->texts->create([
                'lead_id'     => $lead->identifier,
                'from_number' => $from_number,
                'to_number'   => $lead->text_phone,
                'log_message' => $textMessage
            ]);
        });

        // Set Logged Status
        if(!empty($textLog->id)) {
            $status = CampaignSent::STATUS_LOGGED;
        }

        // Handle Transaction
        $sent = null;
        DB::transaction(function() use ($campaign, $lead, &$status, &$textLog, &$sent) {
            // Mark Blast as Sent to Lead
            $sent = $this->campaigns->sent([
                'text_campaign_id' => $campaign->id,
                'lead_id' => $lead->identifier,
                'text_id' => !empty($textLog->id) ? $textLog->id : 0,
                'status' => $status
            ]);
        });

        // Return Sent
        return $sent;
    }
}
