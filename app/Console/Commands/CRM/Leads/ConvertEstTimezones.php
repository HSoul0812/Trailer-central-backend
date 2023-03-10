<?php

namespace App\Console\Commands\CRM\Leads;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Class ConvertEstTimezones
 * @package App\Console\Commands\CRM\Leads
 */
class ConvertEstTimezones extends Command
{
    /**
     * @const string
     */
    const ORIGIN_TIMEZONE = 'America/Indiana/Indianapolis';

    /**
     * @const string
     */
    const OLDEST_DATE_SUBMITTED = 'first day of 3 months ago';


    /**
     * @var string
     */
    protected $signature = "leads:convert-est-timezones {start?}";

    /**
     * @var int
     */
    private $updated = 0;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Get Oldest Date
        $time = $this->argument('start') ?? self::OLDEST_DATE_SUBMITTED;
        $startTime = Carbon::parse($time)->toDateTimeString();
        Log::info('Converting EST Timezones to UTC, Starting From ' . $startTime);

        // Handle Updating Leads Chunked
        DB::table('website_lead')
            ->select('identifier', 'date_submitted')
            ->where('date_submitted', '>', $startTime)
            ->orderBy('date_submitted', 'DESC')
            ->chunk(500, function ($leads) {
                foreach ($leads as $lead) {
                    $origDate = new Carbon($lead->date_submitted, self::ORIGIN_TIMEZONE);
                    $utcDate = $origDate->utc()->toDateTimeString();

                    $result = DB::table('website_lead')
                                ->where(['identifier' => $lead->identifier])
                                ->update(['date_submitted' => $utcDate]);

                    if(!empty($result)) {
                        $this->updated++;
                    }
                }
            });

        Log::info('Converted ' . $this->updated . ' EST Timezones to UTC Submitted After ' . $startTime);
    }
}
