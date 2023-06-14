<?php

namespace App\Console\Commands\Location;

use App\Models\User\DealerLocation;
use App\Models\User\Location\Geolocation;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LatLongDealerPrecisionUpdaterCommand extends Command
{
    private const CHUNK_SIZE = 500;

    /**
     * @var array
     */
    private $geolocationCache;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geolocation:dealers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the precision of the records in the dealer locations table';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->confirm(
            'Are you sure? This would update the dealer_location table in ' .
            env('DB_HOST')
        )) {
            $geolocationsPreloaded = DB::transaction(function () {
                return $this->preloadGeolocation();
            });

            if ($geolocationsPreloaded) {
                DB::transaction(function () {
                    $this->fixLocationWithDataFromGeolocationTable();
                });
            }
        }
        return 0;
    }

    private function fixLocationWithDataFromGeolocationTable()
    {
        $this->info('fixing dealer locations without coordinates with data from the geolocation table');
        $query = DealerLocation::query();

        $progress = $this->output->createProgressBar($query->count());

        $query->chunk(self::CHUNK_SIZE, function (Collection $locations) use ($progress) {
            $locations->each(function ($location) use ($progress) {
                $progress->advance();

                $key = $this->geolocationCacheKey($location->postalcode, $location->country);
                if (isset($this->geolocationCache[$key])) {
                    $cachedGeolocation = $this->geolocationCache[$key];

                    $location->latitude = $cachedGeolocation['latitude'];
                    $location->longitude = $cachedGeolocation['longitude'];
                    $location->geolocation = $cachedGeolocation['geolocation'];
                    $location->postalcode = $cachedGeolocation['zip'];

                    $location->save();
                } else {
                    $this->warn(sprintf('Could not find geolocation for %s %s', $location->country, $location->postalcode));
                }
            });
        });

        $progress->finish();
    }

    private function geolocationCacheKey(string $zipcode, string $country): string
    {
        $country = $country == 'US' ? 'USA' : $country;
        $zipcode = str_replace(' ', '', $zipcode);
        return "{$zipcode}_{$country}";
    }

    private function preloadGeolocation()
    {
        $this->info('Loading coordinates from the geolocation table');

        $query = DB::table(DealerLocation::getTableName())->select(['country', 'postalcode'])
            ->distinct(['country', 'postalcode'])
            ->whereRaw(DB::raw("trim(ifnull(country,'')) <> ''"))
            ->whereRaw(DB::raw("trim(ifnull(postalcode,'')) <> ''"))
            ->whereIn('country', ['USA', 'US', 'CA'])
            ->orderBy('postalcode');

        $progress = $this->output->createProgressBar($query->count());

        $query->chunk(self::CHUNK_SIZE, function ($locations) use ($progress) {
            $zipCodes = $locations->map(function ($location) {
                return $location->postalcode;
            });
            $geolocation = Geolocation::whereIn('zip', $zipCodes->toArray())->get();
            $geolocation->each(function ($location) {
                $key = $this->geolocationCacheKey($location->zip, $location->country);
                if (!isset($this->geolocationCache[$key])) {
                    $this->geolocationCache[$key] = [
                        'zip' => $location->zip,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                        'geolocation' => DB::raw("GeomFromText('" . (new Point($location->latitude, $location->longitude))->toWKT() . "')")
                    ];
                }
            });

            $progress->advance(self::CHUNK_SIZE);
        });

        $progress->finish();

        $this->getOutput()->newLine();

        return true;
    }
}
