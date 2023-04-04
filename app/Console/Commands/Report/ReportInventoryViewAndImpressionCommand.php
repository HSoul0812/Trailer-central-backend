<?php

namespace App\Console\Commands\Report;

use App\Domains\Commands\Traits\PrependsOutput;
use App\Domains\Commands\Traits\PrependsTimestamp;
use App\Domains\UserTracking\Exporters\InventoryViewAndImpressionCsvExporter;
use App\Domains\UserTracking\Mail\ReportInventoryViewAndImpressionEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mail;
use Swift_TransportException;
use Throwable;

class ReportInventoryViewAndImpressionCommand extends Command
{
    use PrependsOutput, PrependsTimestamp;

    const DATE_FORMAT = 'Y-m-d';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:inventory:view-and-impression {date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report the view and impression using the data from the specified date.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(private InventoryViewAndImpressionCsvExporter $exporter)
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

        $date = $this->argument('date');

        try {
            $from = Carbon::createFromFormat(self::DATE_FORMAT, $date)->startOfDay();
        } catch (Throwable) {
            $this->error(sprintf("Invalid date format, accepting only %s format.", self::DATE_FORMAT));

            return 1;
        }

        $to = $from->clone()->endOfDay();

        $filePath = $this->exporter
            ->setFrom($from)
            ->setTo($to)
            ->export();

        $this->info("Csv file is being generated at $filePath!");

        $sendMail = config('trailertrader.report.inventory-view-and-impression.send_mail');

        if ($sendMail) {
            $mailTo = config('trailertrader.report.inventory-view-and-impression.mail_to');

            try {
                Mail::to($mailTo)->send(new ReportInventoryViewAndImpressionEmail($filePath, $date));

                $this->info("Inventory view and impression email sent successfully!");
            } catch (Swift_TransportException $exception) {
                $this->error("Can't sent out email: {$exception->getMessage()}");

                return 2;
            }
        }

        $this->info(sprintf("%s command finished!", $this->name));

        return 0;
    }
}
