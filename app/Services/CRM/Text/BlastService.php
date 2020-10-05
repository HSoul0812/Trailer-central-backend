<?php

namespace App\Services\CRM\Text;

use App\Exceptions\CRM\Text\CustomerLandlineNumberException;
use App\Exceptions\CRM\Text\NoBlastSmsFromNumberException;
use App\Exceptions\CRM\Text\NoLeadsDeliverBlastException;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Text\BlastSent;
use App\Services\CRM\Text\TextServiceInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\CRM\Text\BlastRepositoryInterface;
use App\Repositories\CRM\Text\TemplateRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Class BlastService
 * 
 * @package App\Services\CRM\Text
 */
class BlastService implements BlastServiceInterface
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
     * @var App\Repositories\CRM\Text\BlastRepository
     */
    protected $blasts;

    /**
     * @var App\Repositories\CRM\Text\TemplateRepository
     */
    protected $templates;

    /**
     * @var App\Repositories\User\DealerLocationRepository
     */
    protected $dealerLocation;

    /**
     * BlastService constructor.
     */
    public function __construct(TextServiceInterface $text,
                                LeadRepositoryInterface $leadRepo,
                                TextRepositoryInterface $textRepo,
                                BlastRepositoryInterface $blastRepo,
                                TemplateRepositoryInterface $templateRepo,
                                DealerLocationRepositoryInterface $dealerLocationRepo)
    {
        // Initialize Text Service
        $this->textService = $text;

        // Initialize Repositories
        $this->leads = $leadRepo;
        $this->texts = $textRepo;
        $this->blasts = $blastRepo;
        $this->templates = $templateRepo;
        $this->dealerLocation = $dealerLocationRepo;
    }

    /**
     * Send Blast Text
     * 
     * @param NewDealerUser $dealer
     * @param Blast $blast
     * @return Collection of BlastSent
     */
    public function send($dealer, $blast) {
        // Get From Number
        $from_number = $blast->from_sms_number;
        if(empty($from_number)) {
            $from_number = $this->dealerLocation->findDealerSmsNumber($dealer->id);
            if(empty($from_number)) {
                throw new NoBlastSmsFromNumberException();
            }
        }

        // Get Unsent Blast Leads
        if(count($blast->leads) < 1) {
            throw new NoLeadsDeliverBlastException();
        }

        // Loop Leads for Current Dealer
        $sent = collect([]);
        foreach($blast->leads as $lead) {
            // Not a Valid To Number?!
            if(empty($lead->text_phone)) {
                continue;
            }

            // Send Lead
            $leadSent = $this->sendToLead($from_number, $dealer, $blast, $lead);
            if($leadSent !== null) {
                $sent->push($leadSent);
            }
        }

        // Return Blast Sent Entries
        return $sent;
    }

    /**
     * Send Text to Lead
     * 
     * @param string $from_number sms from number
     * @param NewDealerUser $dealer
     * @param Blast $blast
     * @param Lead $lead
     * @return BlastSent
     */
    private function sendToLead($from_number, $dealer, $blast, $lead) {
        // Get Text Message
        $textMessage = $this->templates->fillTemplate($blast->template->template, [
            'lead_name' => $lead->full_name,
            'title_of_unit_of_interest' => $lead->inventory_title,
            'dealer_name' => $dealer->user->name
        ]);

        try {
            // Send Text
            $this->textService->send($from_number, $lead->text_phone, $textMessage, $lead->full_name);
            $status = BlastSent::STATUS_SENT;
        } catch (CustomerLandlineNumberException $ex) {
            $status = BlastSent::STATUS_LANDLINE;
        } catch (Exception $ex) {
            $status = BlastSent::STATUS_INVALID;
        }

        // Return Sent Result
        return $this->markLeadSent($from_number, $blast, $lead, $textMessage, $status);
    }

    /**
     * Mark Lead as Sent
     * 
     * @param string $from_number sms from number
     * @param Blast $blast
     * @param Lead $lead
     * @param string $textMessage filled text message
     * @param string $status
     * @return BlastSent
     */
    private function markLeadSent($from_number, $blast, $lead, $textMessage, $status) {
        // Handle Transaction
        $textLog = null;
        DB::transaction(function() use ($from_number, $blast, $lead, $textMessage, &$status, &$textLog) {
            // Save Lead Status
            $this->leads->update([
                'id' => $lead->identifier,
                'lead_status' => Lead::STATUS_MEDIUM,
                'next_contact_date' => Carbon::now()->addDay()->toDateTimeString()
            ]);
            $status = BlastSent::STATUS_LEAD;

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
            $status = BlastSent::STATUS_LOGGED;
        }

        // Handle Transaction
        $sent = null;
        DB::transaction(function() use ($blast, $lead, &$status, &$textLog, &$sent) {
            // Mark Blast as Sent to Lead
            $sent = $this->blasts->sent([
                'text_blast_id' => $blast->id,
                'lead_id' => $lead->identifier,
                'text_id' => !empty($textLog->id) ? $textLog->id : 0,
                'status' => $status
            ]);
        });

        // Return Sent
        return $sent;
    }
}
