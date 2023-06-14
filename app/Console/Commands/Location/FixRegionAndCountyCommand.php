<?php

namespace App\Console\Commands\Location;

use App\Models\User\DealerLocation;
use App\Models\User\Location\Geolocation;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FixRegionAndCountyCommand extends Command
{
    private const LOCATION_CHUNK = 100;

    private $geolocationCache;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dealer-location:fix-county-and-region';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command fixes the county and region of the dealer_location with the values from the geolocation table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->confirm(sprintf('This would update the county and region of the dealer_location table on %s. Are you sure?',
            env('DB_HOST')))) {
            $geolocationPreloaded = DB::transaction(function () {
                return $this->preloadGeolocation();
            });

            if ($geolocationPreloaded) {
                DB::transaction(function () {
                    $this->fixCountyAndRegion();
                });
            }
        }

        return 0;
    }

    private function preloadGeolocation(): bool
    {
        $this->info('Preloading locations from geolocation');
        $distinctZipCodes = DB::table(DealerLocation::getTableName())->selectRaw('distinct postalcode, country')->get()->map(function ($location) {
            return $location->postalcode;
        })->toArray();

        $this->geolocationCache = collect([]);
        $geolocationQuery = Geolocation::whereIn('zip', $distinctZipCodes);

        $progressBar = $this->output->createProgressBar($geolocationQuery->count());

        $geolocationQuery->chunk(self::LOCATION_CHUNK, function (Collection $geolocations) use (&$progressBar) {
            $this->geolocationCache = $this->geolocationCache->merge($geolocations->mapWithKeys(function (Geolocation $geolocation) use (&$progressBar) {
                $progressBar->advance();

                return [$this->generateKey($geolocation->zip, $geolocation->country) => [
                    'state' => $geolocation->state
                ]];
            }));
        });
        $progressBar->finish();
        $this->output->newLine();

        return true;
    }

    private function fixCountyAndRegion()
    {
        $dealerLocationQuery = DealerLocation::query();

        $this->info('Fixing dealer locations');
        $progress = $this->output->createProgressBar($dealerLocationQuery->count());
        $dealerLocationQuery->chunk(self::LOCATION_CHUNK, function (Collection $locations) use (&$progress) {
            $locations->each(function (DealerLocation $location) use (&$progress) {
                $geolocation = $this->geolocationCache->get($this->generateKey($location->postalcode, $location->country));
                if ($geolocation) {
                    $location->update([
                        'county' => $geolocation['state'],
                        'region' => $geolocation['state']
                    ]);
                }
                $progress->advance();
            });
        });
        $progress->finish();

        $this->output->newLine();
        $this->info('county and region values fixed!');
    }

    private function generateKey(string $zip, string $country): string
    {
        return "{$zip}_{$country}";
    }
}
