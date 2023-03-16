<?php

namespace App\Console\Commands;

use App\Domains\DealerExports\CreateZipArchiveAction;
use App\Domains\DealerExports\ExportManagerAction;
use App\Models\DealerExport;
use App\Models\User\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Crypt;

/**
 * Class ExportDealerDataCommand
 *
 * @package App\Console\Commands
 */
class ExportDealerDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dms:export-dealer-data {dealerId} {zipPassword}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will create the export csv files for all the DMS related data for the provided user.';

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
     * @return int
     */
    public function handle(): int
    {
        $dealerId = $this->argument('dealerId');
        $zipPassword = $this->argument('zipPassword');

        try {
            $dealer = User::query()->where('dealer_id', $dealerId)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->error('No dealer found!');

            return 1;
        }

        DealerExport::updateOrCreate(
            ['dealer_id' => $dealer->dealer_id, 'entity_type' => 'zip'],
            ['status' => DealerExport::STATUS_IN_PROGRESS, 'zip_password' => Crypt::encryptString($zipPassword), 'file_path' => ''],
        );

        $action = new ExportManagerAction($dealer);

        $action->execute();

        $this->info('Export process has been initiated for the dealer: ' . $dealer->dealer_id);

        $done = false;

        do {
            $this->info('Wait for 10 seconds and see if all jobs are done...');

            sleep(10);

            $allExportCount = DealerExport::query()->where('dealer_id', $dealer->dealer_id)->count();

            $otherEntityExportCount = DealerExport::query()
                ->where('dealer_id', $dealer->dealer_id)
                ->where('entity_type', '!=', 'zip')
                ->where('status', DealerExport::STATUS_PROCESSED)
                ->count();

            if ($otherEntityExportCount === ($allExportCount - 1)) {
                $this->info('Jobs are done, generating the zip file...');

                (new CreateZipArchiveAction($dealer))->execute();

                $done = true;
            } else {
                $this->info('Jobs are not done yet, damn it!');
            }
        } while (!$done);

        $this->info('All is done, have a nice day!');

        return 0;
    }
}
