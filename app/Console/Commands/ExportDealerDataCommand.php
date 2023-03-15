<?php

namespace App\Console\Commands;

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
     * @return mixed
     */
    public function handle()
    {
        $dealerId = $this->argument('dealerId');
        $zipPassword = $this->argument('zipPassword');

        try {
            $dealer = User::query()->where('dealer_id', $dealerId)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $this->error('No dealer found!');
        }

        DealerExport::updateOrCreate(
            ['dealer_id' => $dealer->dealer_id, 'entity_type' => 'zip'],
            ['status' => DealerExport::STATUS_IN_PROGRESS, 'zip_password' => Crypt::encryptString($zipPassword), 'file_path' => ''],
        );

        $action = new ExportManagerAction($dealer);

        $action->execute();

        $this->info('Export process has been initiated for the dealer: ' . $dealer->dealer_id);
        // This is just for the dramatic pause :D
        sleep(1);
        $this->info('Once the process is finished, you can get the file URL from the database.');
    }
}
