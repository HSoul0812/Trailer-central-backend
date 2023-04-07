<?php

namespace App\Console\Commands\UserTracking;

use App\Domains\Commands\Traits\PrependsOutput;
use App\Domains\Commands\Traits\PrependsTimestamp;
use App\Domains\UserTracking\Actions\PopulateMissingWebsiteUserIdAction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class PopulateMissingWebsiteUserIdCommand extends Command
{
    use PrependsOutput, PrependsTimestamp;

    const DATE_FORMAT = 'Y-m-d';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user-tracking:populate-missing-website-user-id {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the missing website user id by date';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private PopulateMissingWebsiteUserIdAction $action)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info(sprintf("%s command started...", $this->name));

        try {
            $from = Carbon::createFromFormat(self::DATE_FORMAT, $this->argument('date'))->startOfDay();
        } catch (Throwable) {
            $this->error(sprintf("Invalid date format, accept only %s format.", self::DATE_FORMAT));

            return 1;
        }

        $to = $from->clone()->endOfDay();

        try {
            $this->action
                ->setFrom($from)
                ->setTo($to)
                ->execute();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return 2;
        }

        $this->info(sprintf("%s command finished!", $this->name));

        return 0;
    }
}
