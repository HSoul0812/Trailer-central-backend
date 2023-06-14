<?php

namespace App\Console\Commands\Database;


use Illuminate\Support\Facades\DB;

class RenameDatabaseTableCommand extends DatabaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:rename-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command renames a database table';

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function handle()
    {
        $existingTable = $this->ask('Table to rename');
        throw_unless(
            $this->tableExists($existingTable),
            new \Exception('table ' . $existingTable . ' does not exist')
        );

        $newTable = $this->ask('New name for table');
        throw_unless(
            $this->tableExists($newTable),
            new \Exception('table ' . $newTable . ' already exists')
        );

        DB::transaction(function () use ($existingTable, $newTable) {
            if ($this->renameTable($existingTable, $newTable)) {
                if ($this->confirm('Do you want to delete ' . $existingTable, false)) {
                    $this->dropTable($existingTable);
                }
            }
        });

        $this->info("Database renamed from $existingTable to $newTable");
        return 0;
    }
}
