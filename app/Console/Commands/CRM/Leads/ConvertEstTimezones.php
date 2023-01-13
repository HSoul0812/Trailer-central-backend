<?php

namespace App\Console\Commands\CRM\Leads;

use Illuminate\Console\Command;

/**
 * Class ConvertEstTimezones
 * @package App\Console\Commands\CRM\Leads
 */
class ConvertEstTimezones extends Command
{
    /**
     * @const string
     */
    const ORIGIN_TIMEZONE = 'America/Indiana/Indianopolis';

    /**
     * @const string
     */
    const OLDEST_DATE_SUBMITTED = 'first day of three months ago';


    /**
     * @var string
     */
    protected $signature = "leads:convert-est-timezones (start?)";

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

        // Handle Updating Leads Chunked
        DB::table('website_lead')
            ->select('identifier', 'date_submitted')
            ->where('date_submitted', '>', $startTime)
            ->chunk(500, function ($leads) {
                foreach ($leads as $lead) {
                    $origDate = new Carbon($lead->date_submitted, self::ORIGIN_TIMEZONE);
                    $utcDate = $origDate->utc()->toDateTimeString();

                    DB::table('website_lead')
                            ->where(['id' => $lead->identifier])
                            ->update(['date_submitted' => $utcDate]);
                }
            });
    }
}
