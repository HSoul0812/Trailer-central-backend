<?php

namespace App\Console\Commands\CRM\Dms\Reports;

use App\Domains\UnitSale\Actions\ExportUnitSalesSummaryCsvAction;
use App\Models\User\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class ExportUnitSalesSummaryCsv extends Command
{
    const TIME_FORMAT = 'Y-m-d';

    protected $signature = "
        crm:dms:reports:unit-sales-summary
        {dealerId : Dealer ID to send report.}
        {from : Get data from this date, format: Y-m-d, the command will use startOfDate.}
        {to : Get data to this date, format: Y-m-d, the command will use endOfDate.}
    ";

    protected $description = 'Generate the unit sales summary report as CSV file from the given dealer ID and the time range';

    public function handle(): int
    {
        try {
            list($dealer, $from, $to) = $this->prepareData();

            $path = resolve(ExportUnitSalesSummaryCsvAction::class)
                ->fromDealer($dealer)
                ->from($from)
                ->to($to)
                ->execute();
        } catch (Throwable $e) {
            $this->error("Error: " . $e->getMessage());
            return 1;
        }

        $this->info("CSV Path: $path");

        return 0;
    }

    /**
     * Prepare the command data
     *
     * @return array
     */
    private function prepareData(): array
    {
        return [
            User::findOrFail($this->argument('dealerId')),
            Carbon::createFromFormat(self::TIME_FORMAT, $this->argument('from'))->startOfDay(),
            Carbon::createFromFormat(self::TIME_FORMAT, $this->argument('to'))->endOfDay()
        ];
    }
}
