<?php

namespace App\Console\Commands\CRM\Text;

use Illuminate\Console\Command;
use App\Models\User\NewDealerUser;
use App\Services\CRM\Text\BlastServiceInterface;
use App\Repositories\CRM\Text\BlastRepositoryInterface;
use App\Repositories\CRM\Text\TextRepositoryInterface;

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
     * @var BlastServiceInterface
     */
    protected $service;

    /**
     * @var TextRepositoryInterface
     */
    protected $texts;

    /**
     * @var BlastRepositoryInterface
     */
    protected $blasts;

    /**
     * @var \DateTime
     */
    protected $datetime = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(BlastServiceInterface $service,
                                TextRepositoryInterface $textRepo,
                                BlastRepositoryInterface $blastRepo)
    {
        parent::__construct();

        $this->service = $service;
        $this->texts = $textRepo;
        $this->blasts = $blastRepo;
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
            if(!empty($dealerId)) {
                $dealers = NewDealerUser::where('id', $dealerId)->with('user')->get();
            } else {
                $dealers = NewDealerUser::has('activeCrmUser')->with('user')->get();
            }
            $this->info("{$command} found " . count($dealers) . " dealers to process");

            // Get Dealers With Active CRM
            foreach($dealers as $dealer) {
                // Get Blasts for Dealer
                $blasts = $this->blasts->getAllActive($dealer->user_id);
                if(count($blasts) < 1) {
                    continue;
                }

                // Loop Blasts for Current Dealer
                $this->info("{$command} dealer #{$dealer->id} found " . count($blasts) . " active blasts to process");
                foreach($blasts as $blast) {
                    // Try Catching Error for Blast
                    try {
                        // Send Campaign
                        $this->service->send($dealer, $blast);
                    } catch(\Exception $e) {
                        $this->error("{$command} exception returned on blast #{$blast->id} {$e->getMessage()}: {$e->getTraceAsString()}");
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
