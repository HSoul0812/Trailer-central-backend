<?php

namespace App\Console\Commands\Database;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Class PruneSSNCommand
 *
 * @package App\Console\Commands\Database
 */
class PruneSSNCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = '
        database:prune-ssn
        {--olderThanDays=30 : To prune SSN older than specifed value, default is 30 days.}
    ';

    /**
     * @var string
     */
    protected $description = 'To find and prune SSN from the database.';

    /**
     * @return int
     */
    public function handle(): int
    {
        $days = $this->option('olderThanDays');

        if (!is_numeric($days)) {
            $this->info('Specified values for days is not valid! Terminating the script now, have a good day! 😃');

            return 1;
        }

        $selectedDate = now()->subDays($days)->endOfDay();

        $tableData = $this->getTableFields($selectedDate);

        foreach ($tableData as $key => $value) {
            $infoMsg = 'SSN data created before "' . $selectedDate . '" in "' . $value['tableName'] . '" table.';
            $this->info('Removing ' . $infoMsg);

            DB::table($value['tableName'])
                ->where($value['whereCondition'])
                ->update($value['updateData']);

            $this->info('Removed ' . $infoMsg);
        }

        $this->info('The command has finished!');

        return 0;
    }

    /**
     * @return array
     *
     * @param mixed $selectedDate
     */
    private function getTableFields($selectedDate): array
    {
        return [
            [
                'tableName' => 'website_lead_fandi',
                'updateData' => [
                    'ssn_no' => '',
                ],
                'whereCondition' => [
                    ['date_imported', '<', $selectedDate],
                ],
            ],
            /* [
                'tableName' => 'dealer_employee',
                'updateData' => [
                    'ssn' => null,
                ],
                'whereCondition' => [
                    ['date_imported', '<', $selectedDate],
                ],
            ], */
        ];
    }
}
