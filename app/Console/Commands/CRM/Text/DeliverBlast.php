<?php

namespace App\Console\Commands\CRM\Text;

use Illuminate\Console\Command;
use App\Models\User\NewDealerUser;
use App\Services\CRM\Text\TextServiceInterface;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Repositories\CRM\Text\BlastRepositoryInterface;
use App\Repositories\CRM\Text\TemplateRepositoryInterface;
use App\Repositories\CRM\Text\TextRepositoryInterface;
use App\Repositories\User\DealerLocationRepositoryInterface;

class DeliverBlast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'text:deliver-blast {dealer?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process sending texts to all leads on all active blasts.';

    /**
     * @var App\Services\CRM\Text\TextServiceInterface
     */
    protected $service;

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
     * @var datetime
     */
    protected $datetime = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TextServiceInterface $service, LeadRepositoryInterface $leadRepo,
                                BlastRepositoryInterface $blastRepo, TemplateRepositoryInterface $templateRepo,
                                TextRepositoryInterface $textRepo, DealerLocationRepositoryInterface $dealerLocationRepo)
    {
        parent::__construct();

        $this->service = $service;
        $this->leads = $leadRepo;
        $this->texts = $textRepo;
        $this->blasts = $blastRepo;
        $this->templates = $templateRepo;
        $this->dealerLocation = $dealerLocationRepo;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get Dealer ID
        $dealerId = $this->argument('dealer');

        // Initialize Time
        date_default_timezone_set(env('DB_TIMEZONE'));
        $this->datetime = new \DateTime();
        $this->datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));

        // Try Catching Error for Whole Script
        try {
            // Log Start
            $now = $this->datetime->format("l, F jS, Y");
            $command = "text:deliver-blast" . (!empty($dealerId) ? ' ' . $dealerId : '');
            $this->info("{$command} started {$now}");

            // Handle Dealer Differently
            $dealers = array();
            if(!empty($dealerId)) {
                $dealers = NewDealerUser::where('id', $dealerId)->with('user')->get();
            } else {
                $dealers = NewDealerUser::has('activeCrmUser')->has('salespeopleEmails')->with('user')->get();
            }
            $this->info("{$command} found " . count($dealers) . " dealers to process");

            // Get Dealers With Valid Salespeople
            foreach($dealers as $dealer) {
                // Get Unassigned Leads
                $blasts = $this->blasts->getAll([
                    'is_cancelled' => false,
                    'is_delivered' => false,
                    'send_date' => 'due_now',
                    'per_page' => 'all',
                    'user_id' => $dealer->user_id
                ]);
                if(count($blasts) < 1) {
                    continue;
                }

                // Loop Blasts for Current Dealer
                $this->info("{$command} dealer #{$dealer->id} found " . count($blasts) . " active blasts to process");
                foreach($blasts as $blast) {
                    // Get From Number
                    $from_number = $blast->from_sms_number;
                    if(empty($from_number)) {
                        $from_number = $this->dealerLocation->findDealerNumber($lead->dealer_id, $lead->preferred_location);
                        if(empty($from_number)) {
                            continue;
                        }
                    }

                    // Get Unsent Blast Leads
                    $leads = $this->blasts->getLeads([
                        'per_page' => 'all',
                        'id' => $blast->id
                    ]);
                    if(count($leads) < 1) {
                        continue;
                    }

                    // Get Template!
                    $template = $blast->template->template;

                    // Loop Leads for Current Dealer
                    $this->info("{$command} dealer #{$dealer->id} blast {$blast->blast_name} found " . count($leads) . " leads to process");
                    foreach($leads as $lead) {
                        // If Error Occurs, Skip
                        try {
                            // Initialize Notes Array
                            $leadName = $lead->id_name;

                            // Get To Numbers
                            $to_number = $lead->text_phone;
                            if(empty($to_number)) {
                                continue;
                            }
                            $to_number = "+12626619236"; // DEBUG OVERRIDE

                            // Get Text Message
                            $textMessage = $this->templates->fillTemplate($template, [
                                'lead_name' => $lead->full_name,
                                'title_of_unit_of_interest' => $lead->inventory->title,
                                'dealer_name' => $dealer->user->name
                            ]);
                            $this->info("{$command} preparing to send text to {$leadName} at {$to_number}");

                            // Send Text
                            $this->service->send($from_number, $to_number, $textMessage, $lead->full_name);
                            $this->info("{$command} send text to {$leadName} at {$to_number}");
                            $status = 'sent';

                            // If ANY Errors Occur, Make Sure Text Still Gets Marked Sent!
                            try {
                                // Save Lead Status
                                $this->texts->updateLeadStatus($lead);
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
                            $this->blasts->sent([
                                'text_blast_id' => $blast->id,
                                'lead_id' => $lead->identifier,
                                'text_id' => !empty($textLog->id) ? $textLog->id : 0,
                                'status' => $status
                            ]);
                            $this->info("{$command} inserted blast sent for lead {$leadName}");
                        } catch(\Exception $e) {
                            $this->error("{$command} exception returned trying to send blast text {$e->getMessage()}: {$e->getTraceAsString()}");
                        }
                        die;
                    }
                }
            }
        } catch(\Exception $e) {
            $this->error("{$command} exception returned {$e->getMessage()}: {$e->getTraceAsString()}");
        }

        // Log End
        $datetime = new \DateTime();
        $datetime->setTimezone(new \DateTimeZone(env('DB_TIMEZONE')));
        $this->info("{$command} finished on " . $datetime->format("l, F jS, Y"));
    }
}
