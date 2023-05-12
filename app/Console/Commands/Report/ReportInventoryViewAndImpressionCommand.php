<?php

namespace App\Console\Commands\Report;

use App\Domains\Commands\Traits\PrependsOutput;
use App\Domains\Commands\Traits\PrependsTimestamp;
use App\Domains\Compression\Actions\CompressFileWithGzipAction;
use App\Domains\Compression\Exceptions\GzipFailedException;
use App\Domains\UserTracking\Exporters\InventoryViewAndImpressionCsvExporter;
use App\Domains\UserTracking\Mail\ReportInventoryViewAndImpressionEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mail;
use Swift_TransportException;
use Throwable;

class ReportInventoryViewAndImpressionCommand extends Command
{
    use PrependsOutput;
    use PrependsTimestamp;

    public const DATE_FORMAT = 'Y-m-d';

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
     */
    public function __construct(
        private InventoryViewAndImpressionCsvExporter $exporter,
        private CompressFileWithGzipAction $compressFileWithGzipAction,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info(sprintf('%s command started...', $this->name));

        $date = $this->argument('date');

        try {
            $from = Carbon::createFromFormat(self::DATE_FORMAT, $date)->startOfDay();
        } catch (Throwable) {
            $this->error(sprintf('Invalid date format, accepting only %s format.', self::DATE_FORMAT));

            return 1;
        }

        $to = $from->clone()->endOfDay();

        $csvFilePath = $this->exporter
            ->setFrom($from)
            ->setTo($to)
            ->export();

        $this->info("Csv file is being generated at $csvFilePath! Now we zip it...");

        try {
            $zipFilePath = $this->compressFileWithGzipAction->execute($csvFilePath);
        } catch (GzipFailedException $e) {
            $this->error($e->getMessage());

            return 3;
        }

        $this->info("Zip file is being generated at $zipFilePath!");

        $sendMail = config('trailertrader.report.inventory-view-and-impression.send_mail');

        if ($sendMail) {
            $mailTo = config('trailertrader.report.inventory-view-and-impression.mail_to');

            try {
                Mail::to($mailTo)->send(new ReportInventoryViewAndImpressionEmail($zipFilePath, $date));

                @unlink($csvFilePath);

                $this->info('Inventory view and impression email sent successfully!');
            } catch (Swift_TransportException $exception) {
                $this->error("Can't sent out email: {$exception->getMessage()}");

                @unlink($csvFilePath);

                return 2;
            }
        }

        $this->info(sprintf('%s command finished!', $this->name));

        return 0;
    }
}
