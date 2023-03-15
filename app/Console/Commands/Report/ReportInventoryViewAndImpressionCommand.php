<?php

namespace App\Console\Commands\Report;

use App\Domains\UserTracking\Exporters\InventoryViewAndImpressionCsvExporter;
use App\Domains\UserTracking\Mail\ReportInventoryViewAndImpressionEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mail;
use Throwable;

class ReportInventoryViewAndImpressionCommand extends Command
{
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

        $sendMail = config('trailertrader.report.inventory-view-and-impression.send_mail');

        if ($sendMail) {
            $mailTo = config('trailertrader.report.inventory-view-and-impression.mail_to');

            Mail::to($mailTo)->send(new ReportInventoryViewAndImpressionEmail($filePath, $date));
        }

        return 0;
    }
}
