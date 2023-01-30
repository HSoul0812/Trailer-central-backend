<?php

namespace App\Console\Commands\Database;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Console\Traits\PrependsOutput;
use App\Console\Traits\PrependsTimestamp;

/**
 * Class PruneSSNCommand
 *
 * @package App\Console\Commands\Database
 */
class PruneSSNCommand extends Command
{
    use PrependsOutput, PrependsTimestamp;

    /**
     * @var string
     */
    protected $signature = '
        database:prune-ssn
        {--olderThanDays=30 : To prune SSN older than specified value, default is 30 days.}
        {--chunkSize=1000 : The size for each chunk.}
        {--delay=5 : Delay time in seconds after each delayChunkCount parts is being dispatched.}
    ';

    const WEBSITE_FORM_SUBMISSIONS_TABLE_SSN_KEY = 'ssn';

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
            $this->line('Specified values for days is not valid! Terminating the script now, have a good day! 😃');

            return 1;
        }

        $selectedDate = now()->subDays($days)->endOfDay();

        $tableData = $this->getTableFields($selectedDate);

        foreach ($tableData as $key => $value) {
            $infoMsg = 'SSN data created before "' . $selectedDate . '" in "' . $value['tableName'] . '" table.';
            $this->line('Removing ' . $infoMsg);

            DB::table($value['tableName'])
                ->where($value['whereCondition'])
                ->update($value['updateData']);

            $this->line('Removed ' . $infoMsg);
        }

        $chunkSize = $this->option('chunkSize');
        $delay = $this->option('delay');

        $infoMsg = 'SSN data created before "' . $selectedDate . '" in "website_form_submissions" table.';
        $this->line('Removing ' . $infoMsg);

        DB::table('website_form_submissions')
            ->select('id', 'answers')
            ->where([
                ['created_at', '<', $selectedDate],
                ['is_ssn_removed', '=', false],
            ])
            ->whereNotNull('answers')
            ->whereRaw("answers LIKE '%ssn%'")
            ->orderBy('id')
            ->chunkById($chunkSize, function (Collection $submissions) use ($delay) {
                foreach ($submissions as $submission) {
                    try {
                        if (self::isValidJson($submission->answers)) {
                            $alteredAnswer = null;
                            $answersCollection = collect(json_decode($submission->answers, true));
                            if($answersCollection->isNotEmpty() & $answersCollection->contains('name', 'ssn')) {
                                $alteredAnswer = $answersCollection->reject(function ($value, $key) {
                                    return $value['name'] === 'ssn';
                                });

                                DB::table('website_form_submissions')
                                    ->where('id', '=', $submission->id)
                                    ->update(['answers' => $alteredAnswer->toJson()]);
                            }
                        } else {
                            $this->line('Invalid JSON for id: ' . $submission->id);
                        }
                    } catch (\Throwable $th) {
                        $this->line($th->getMessage());
                    }
                }

                // We will sleep for a certain seconds
                sleep($delay);
            });

        $this->line('Removed ' . $infoMsg);

        $this->line('The command has finished!');

        return 0;
    }

    private static function isValidJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
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
        ];
    }
}
