<?php

namespace App\Console\Commands;

use App\Domains\UserTracking\Exporters\InventoryViewAndImpressionCsvExporter;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class ReportInventoryViewAndImpressionCommand extends Command
{
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
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param InventoryViewAndImpressionCsvExporter $exporter
     * @return int
     */
    public function handle(InventoryViewAndImpressionCsvExporter $exporter): int
    {
        $dateFormat = 'Y-m-d';
        $date = $this->argument('date');

        try {
            $from = Carbon::createFromFormat($dateFormat, $date)->startOfDay();
        } catch (Throwable) {
            $this->error("Invalid date format, accepting only Y-m-d format.");

            return 1;
        }

        $to = $from->clone()->endOfDay();

        $exporter
            ->setFrom($from)
            ->setTo($to)
            ->export();

        return 0;
    }
}
