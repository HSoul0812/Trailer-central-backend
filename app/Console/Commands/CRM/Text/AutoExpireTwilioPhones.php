<?php

namespace App\Console\Commands\CRM\Text;

use App\Repositories\CRM\Text\NumberRepositoryInterface;
use App\Services\CRM\Text\TwilioServiceInterface;
use Illuminate\Console\Command;
use Carbon\Carbon;

class AutoExpireTwilioPhones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'text:auto-expire-phones';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically release phones that are currently not being used.';

    /**
     * @var TwilioServiceInterface
     */
    protected $service;

    /**
     * @var App\Repositories\CRM\Text\NumberRepositoryInterface
     */
    protected $numbers;

    /**
     * @var datetime
     */
    protected $datetime = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(TwilioServiceInterface $service, NumberRepositoryInterface $numbers)
    {
        parent::__construct();

        $this->service = $service;
        $this->numbers = $numbers;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get Current Timestamp
        $numbersExpiredToDate = Carbon::now()->timestamp;
        $this->info("Delete phone numbers expired/unused from twilio...");

        // Get All Expired Twilio Numbers From DB
        $this->numbers->getAllExpiredChunked(function($numbers) {
            foreach($numbers as $number) {
                $this->info("Processing expired number {$number->phone_number}");
                $this->service->delete($number->phone_number);
            }
        }, $numbersExpiredToDate);

        // Get All Numbers From Twilio Missing in Our DB
        $numbers = $this->service->missing(500);
        foreach($numbers as $number) {
            $this->info("Processing number {$number} missing from DB");
            $this->service->delete($number);
        }
    }
}
