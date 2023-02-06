<?php

namespace App\Console\Commands\Database;

use App\Console\Traits\PrependsOutput;
use App\Console\Traits\PrependsTimestamp;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class PruneSSNCommand
 *
 * @package App\Console\Commands\Database
 */
class PruneSSNCommand extends Command
{
    use PrependsOutput, PrependsTimestamp;

    const WEBSITE_FORM_SUBMISSIONS_TABLE_SSN_KEY = 'ssn';

    const DEFAULT_CHUNK_SIZE = 1000;
    const DEFAULT_DELAY = 5;
    const DEFAULT_OLDER_THAN_DAYS = 30;

    /**
     * @var string
     */
    protected $signature = '
        database:prune-ssn
        {--olderThanDays=' . self::DEFAULT_OLDER_THAN_DAYS .
        ' : To prune SSN older than specified value, default is 30 days.}
        {--chunkSize=' . self::DEFAULT_CHUNK_SIZE .
        ' : The size for each chunk.}
        {--delay=' . self::DEFAULT_DELAY .
        ' : Delay time in seconds after each delayChunkCount parts is being dispatched.}
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
            $this->line('Specified values for days is not valid! Terminating the script now, have a good day! 😃');

            return 1;
        }

        $selectedDate = now()->subDays($days)->endOfDay();

        $tableData = $this->getTableFields($selectedDate);

        foreach ($tableData as $key => $value) {
            $infoMsg = 'SSN data, created before "' . $selectedDate . '" in "' . $value['tableName'] . '" table.';
            $this->line('Removing ' . $infoMsg);

            DB::table($value['tableName'])
                ->where($value['whereCondition'])
                ->update($value['updateData']);

            $this->info('Removed 🗑️ ' . $infoMsg);
        }

        $chunkSize = $this->option('chunkSize');
        $delay = $this->option('delay');

        $infoMsg = 'SSN data, created before "' . $selectedDate . '" in "website_form_submissions" table.';
        $this->line('Removing ' . $infoMsg);

        DB::table('website_form_submissions')
            ->select(['id', 'answers', 'is_ssn_removed', ])
            ->where([
                ['created_at', '<', $selectedDate],
                ['is_ssn_removed', '=', false],
            ])
            ->orderBy('id')
            ->chunkById($chunkSize, function (Collection $submissions) use ($delay) {
                $this->line('Processing for: ' . $submissions->pluck('id')->implode(', '));
                foreach ($submissions as $submission) {
                    try {
                        $updateData = [
                            'is_ssn_removed' => true,
                        ];

                        if (self::isValidJson($submission->answers)) {
                            $answersCollection = collect(json_decode($submission->answers, true));
                            if ($answersCollection->isNotEmpty() &&
                                $answersCollection->contains('name', self::WEBSITE_FORM_SUBMISSIONS_TABLE_SSN_KEY)
                            ) {
                                $ssnAnswers = $answersCollection->all();
                                $answersCollection->where('name', self::WEBSITE_FORM_SUBMISSIONS_TABLE_SSN_KEY)
                                    ->each(function ($item, $key) use (&$ssnAnswers, $submission) {
                                        $ssnAnswers = data_set($ssnAnswers, $key . '.answer', '');
                                    });

                                $updateData['answers'] = json_encode($ssnAnswers);
                            }
                        } else {
                            $this->warn('Invalid JSON for id: ' . $submission->id);
                        }

                        DB::table('website_form_submissions')
                            ->where('id', '=', $submission->id)
                            ->update($updateData);
                    } catch (\Throwable $th) {
                        $this->error($th->getMessage());
                    }
                }

                // We will sleep for a certain seconds
                sleep($delay);
            });

        $this->info('Removed 🗑️ ' . $infoMsg);

        $this->info('The command has finished!');

        return 0;
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    private static function isValidJson(string $string): bool
    {
        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * @param mixed $selectedDate
     *
     * @return array
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
