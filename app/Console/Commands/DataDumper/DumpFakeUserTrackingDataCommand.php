<?php

namespace App\Console\Commands\DataDumper;

use App\Domains\Commands\Traits\PrependsOutput;
use App\Domains\Commands\Traits\PrependsTimestamp;
use App\Domains\UserTracking\Actions\GetPageNameFromUrlAction;
use App\Domains\UserTracking\Types\UserTrackingEvent;
use App\Models\UserTracking;
use Carbon\Carbon;
use DB;
use Faker\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

class DumpFakeUserTrackingDataCommand extends Command
{
    use PrependsOutput;
    use PrependsTimestamp;

    public const DATE_FORMAT = 'Y-m-d';

    protected $signature = '
        data-dumper:user-tracking
        {from : From [in Y-m-d format]}
        {to : To [in Y-m-d format]}
        {dataPointPerDay : How many data point should I fake per day?}
    ';

    protected $description = 'Dump the fake user tracking data to the database.';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        if (config('app.env') === 'production') {
            $this->error('Running this command in production environment is prohibited.');

            return 1;
        }

        $from = Carbon::createFromFormat(self::DATE_FORMAT, $this->argument('from'))->startOfDay();
        $to = Carbon::createFromFormat(self::DATE_FORMAT, $this->argument('to'))->endOfDay();

        $dataPointPerDay = $this->argument('dataPointPerDay');

        if (filter_var($dataPointPerDay, FILTER_VALIDATE_INT) === false) {
            $this->error('The dataPointPerDay argument must be an integer.');

            return 2;
        }

        $totalProcessed = 0;

        $faker = Factory::create();

        while ($from->lte($to)) {
            DB::beginTransaction();

            for ($i = 0; $i < $dataPointPerDay; $i++) {
                $randomMetaCount = $faker->randomElement([
                    1,
                    15,
                    30,
                    60,
                ]);

                $meta = [];

                for ($j = 0; $j < $randomMetaCount; $j++) {
                    $meta[] = [
                        'stock' => Str::random(10),
                        'title' => $faker->sentence(),
                        'type_id' => $faker->numberBetween(1, 5),
                        'category' => $faker->word(),
                        'dealer_id' => $faker->numberBetween(1, 1000),
                        'type_label' => $faker->word(),
                        'inventory_id' => $faker->numberBetween(1, 10000),
                        'category_label' => $faker->word(),
                    ];
                }

                $event = $faker->randomElement([
                    UserTrackingEvent::PAGE_VIEW,
                    UserTrackingEvent::IMPRESSION,
                ]);

                $pageName = $faker->word();

                if ($event === UserTrackingEvent::IMPRESSION) {
                    $pageName = $faker->randomElement(GetPageNameFromUrlAction::PAGE_NAMES);
                }

                $createdAtTimestamp = $faker->numberBetween($from->clone()->startOfDay()->timestamp, $from->clone()->endOfDay()->timestamp);

                UserTracking::factory()->create([
                    'event' => $event,
                    'page_name' => $pageName,
                    'meta' => $meta,
                    'created_at' => Carbon::createFromTimestamp($createdAtTimestamp),
                ]);
            }

            DB::commit();

            $totalProcessed += $dataPointPerDay;

            $this->info("Total added: $totalProcessed records.");

            $from->addDay();
        }

        return 0;
    }
}
