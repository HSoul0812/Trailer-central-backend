<?php

namespace App\Services\CRM\Text;

use App\Exceptions\CRM\Text\CustomerLandlineNumberException;
use App\Models\CRM\Leads\Lead;
use App\Repositories\CRM\Text\BlastRepositoryInterface;
use App\Repositories\CRM\Text\TemplateRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;

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
                                BlastRepositoryInterface $blastRepo,
                                TemplateRepositoryInterface $templateRepo,
                                DealerLocationRepositoryInterface $dealerLocationRepo)
    {
        // Initialize Text Service
        $this->texts = $text;

        // Initialize Repositories
        $this->blasts = $blastRepo;
        $this->templates = $templateRepo;
        $this->dealerLocation = $dealerLocationRepo;
    }

    /**
     * Send Blast Text
     * 
     * @param string $command that was run
     * @param NewDealerUser $dealer
     * @param Blast $blast
     * @return false || array of BlastSent
     */
    public function send($command, $dealer, $blast) {
        // Get From Number
        $from_number = $blast->from_sms_number;
        if(empty($from_number)) {
            $from_number = $this->dealerLocation->findDealerSmsNumber($dealer->id);
            if(empty($from_number)) {
                return false;
            }
        }

        // Get Unsent Blast Leads
        if(count($blast->leads) < 1) {
            return false;
        }

        // Get Template!
        $template = $blast->template->template;

        // Loop Leads for Current Dealer
        $this->info("{$command} dealer #{$dealer->id} blast {$blast->blast_name} found " . count($blast->leads) . " leads to process");
        $sent = array();
        foreach($blast->leads as $lead) {
            // Not a Valid To Number?!
            if(empty($lead->text_phone)) {
                continue;
            }

            // Send Lead
            $sent[] = $this->sendToLead($command, $from_number, $template, $dealer, $blast, $lead);
        }

        // Return Blast Sent Entries
        return $sent;
    }

    /**
     * Send Text to Lead
     * 
     * @param string $command that was run
     * @param string $from_number sms from number
     * @param string $template text parsed
     * @param NewDealerUser $dealer
     * @param Blast $blast
     * @param Lead $lead
     * @return false || BlastSent
     */
    public function sendToLead($command, $from_number, $template, $dealer, $blast, $lead) {
        // Initialize Notes Array
        $leadName = $lead->id_name;

        // Get To Numbers
        $to_number = $lead->text_phone;

        // Get Text Message
        $textMessage = $this->templates->fillTemplate($template, [
            'lead_name' => $lead->full_name,
            'title_of_unit_of_interest' => $lead->inventory->title,
            'dealer_name' => $dealer->user->name
        ]);
        $this->info("{$command} preparing to send text to {$leadName} at {$to_number}");

        try {
            // Send Text
            $this->texts->send($from_number, $to_number, $textMessage, $lead->full_name);
            $this->info("{$command} send text to {$leadName} at {$to_number}");
            $status = 'sent';
        } catch (CustomerLandlineNumberException $ex) {
            $status = 'landline';
            $this->error("{$command} exception returned, phone number {$to_number} cannot receive texts!");
        } catch (Exception $ex) {
            $status = 'invalid';
            $this->error("{$command} exception returned trying to send blast {$e->getMessage()}: {$e->getTraceAsString()}");
        }

        // If ANY Errors Occur, Make Sure Text Still Gets Marked Sent!
        try {
            // Save Lead Status
            $this->leads->update([
                'id' => $lead->identifier,
                'lead_status' => Lead::STATUS_MEDIUM,
                'next_contact_date' => Carbon::now()->addDay()->toDateTimeString()
            ]);
            $this->info("{$command} updated lead {$leadName} status");
            $status = 'lead';

            // Log SMS
            $textLog = $this->texts->create([
                'lead_id'     => $lead->identifier,
                'from_number' => $from_number,
                'to_number'   => $to_number,
                'log_message' => $textMessage
            ]);
            $this->info("{$command} logged text for {$leadName} at {$to_number}");
            $status = 'logged';
        } catch(\Exception $e) {
            $this->error("{$command} exception returned after blast sent {$e->getMessage()}: {$e->getTraceAsString()}");
        }

        // Mark Blast as Sent to Lead
        $sent = $this->blasts->sent([
            'text_blast_id' => $blast->id,
            'lead_id' => $lead->identifier,
            'text_id' => !empty($textLog->id) ? $textLog->id : 0,
            'status' => $status
        ]);
        $this->info("{$command} inserted blast sent for lead {$leadName} and blast {$blast->id}");

        // Return Sent
        return $sent;
    }
}
