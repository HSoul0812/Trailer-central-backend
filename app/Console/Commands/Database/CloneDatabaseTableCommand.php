<?php

namespace App\Console\Commands\Database;

use Illuminate\Support\Facades\DB;

class CloneDatabaseTableCommand extends DatabaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:clone-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command clones a database table';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function handle()
    {
        $existingTable = $this->ask('Table to clone');
        throw_unless(
            $this->tableExists($existingTable),
            new \Exception('table ' . $existingTable . ' does not exist')
        );

        $newTable = $this->ask('New name for table', $existingTable . '_clone');
        throw_if(
            $this->tableExists($newTable),
            new \Exception('table ' . $newTable . ' already exists')
        );

        DB::transaction(function () use ($existingTable, $newTable) {
            if ($this->cloneTable($existingTable, $newTable)) {
                $this->migrateTableData($existingTable, $newTable);
            }
        });

        $this->info($existingTable . ' cloned into ' . $newTable);
        return 0;
    }
}
