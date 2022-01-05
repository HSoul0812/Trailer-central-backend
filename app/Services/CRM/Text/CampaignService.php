<?php

namespace App\Services\CRM\Text;

use App\Exceptions\CRM\Text\CustomerLandlineNumberException;
use App\Exceptions\CRM\Text\NoCampaignSmsFromNumberException;
use App\Exceptions\CRM\Text\NoLeadsProcessCampaignException;
use App\Models\CRM\Interactions\TextLog;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadStatus;
use App\Models\CRM\Text\Campaign;
use App\Models\CRM\Text\CampaignSent;
use App\Models\User\NewDealerUser;
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
        $from_number = $this->getFromNumber($dealer->id, $campaign);

        // Get Unsent Campaign Leads
        if(count($campaign->leads) < 1) {
            $this->log->error('No Leads found for Campaign #' . $campaign->id . ' for Dealer #: ' . $dealer->id);
            throw new NoLeadsProcessCampaignException;
        }

        // Loop Leads for Current Dealer
        $sent = new Collection();
        $this->log->debug('Found ' . $campaign->leads->count() . ' Leads for Campaign #' . $campaign->id);
        foreach($campaign->leads as $lead) {
            // Not a Valid To Number?!
            if(empty($lead->text_phone)) {
                continue;
            }

            // Send Lead
            $leadSent = $this->sendToLead($from_number, $dealer, $campaign, $lead);
            if($leadSent !== null) {
                $sent->push($leadSent);
            }
        }

        // Return Campaign Sent Entries
        return $sent;
    }


    /**
     * Get From Number
     * 
     * @param int $dealerId
     * @param Campaign $campaign
     * @throw NoCampaignSmsFromNumberException
     * @return string
     */
    private function getFromNumber(int $dealerId, Campaign $campaign): string {
        // Get From Number
        $chosenNumber = $campaign->from_sms_number;
        if(!empty($chosenNumber)) {
            return $chosenNumber;
        }

        // Get First Available Number From Dealer Location
        $defaultNumber = $this->dealerLocation->findDealerSmsNumber($dealerId);
        if(!empty($defaultNumber)) {
            return $defaultNumber;
        }

        // Throw Exception
        $this->log->error('No Campaign SMS From Number for Dealer #: ' . $dealerId);
        throw new NoCampaignSmsFromNumberException;
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

            // Update Lead Status
            if($this->updateLead($lead)) {
                $status = CampaignSent::STATUS_LEAD;
            }

            // Save Text to DB
            $textLog = $this->saveText($from_number, $lead, $textMessage);
            if(!empty($textLog->id)) {
                $status = CampaignSent::STATUS_LOGGED;
            }
        } catch (CustomerLandlineNumberException $ex) {
            $status = CampaignSent::STATUS_LANDLINE;
            $this->log->error('Could not send text to number ' . $from_number . ': ' . $ex->getMessage());
        } catch (\Exception $ex) {
            $status = CampaignSent::STATUS_INVALID;
            $this->log->error('Exception returned trying to send text: ' . $ex->getMessage() .
                                PHP_EOL . $ex->getTraceAsString());
        }

        // Return Sent Result
        return $this->markLeadSent($campaign, $lead, $status, $textLog ?? null);
    }

    /**
     * Update Lead Status
     * 
     * @param Lead $lead
     * @return LeadStatus
     */
    private function updateLead(Lead $lead): LeadStatus {
        // Save Lead Status
        return $this->leadStatus->createOrUpdate([
            'lead_id' => $lead->identifier,
            'status' => Lead::STATUS_MEDIUM,
            'next_contact_date' => Carbon::now()->addDay()->toDateTimeString()
        ]);
    }

    /**
     * Save Text to DB
     * 
     * @param string $from_number sms from number
     * @param Lead $lead
     * @param string $textMessage filled text message
     * @return TextLog
     */
    private function saveText(string $from_number, Lead $lead, string $textMessage): TextLog {
        // Log SMS
        return $this->texts->create([
            'lead_id'     => $lead->identifier,
            'from_number' => $from_number,
            'to_number'   => $lead->text_phone,
            'log_message' => $textMessage
        ]);
    }

    /**
     * Mark Lead as Sent
     * 
     * @param Campaign $campaign
     * @param Lead $lead
     * @param string $status
     * @param null|TextLog $textLog
     * @return null|CampaignSent
     */
    private function markLeadSent(Campaign $campaign, Lead $lead, string $status,
                                    ?TextLog $textLog = null): ?CampaignSent {
        // Mark Campaign as Sent to Lead
        try {
            return $this->campaigns->sent([
                'text_blast_id' => $campaign->id,
                'lead_id' => $lead->identifier,
                'text_id' => !empty($textLog->id) ? $textLog->id : 0,
                'status' => $status
            ]);
        } catch(\Exception $ex) {
            $this->log->error('Failed to mark lead as sent: ' . $ex->getMessage());
            return null;
        }
    }
}
