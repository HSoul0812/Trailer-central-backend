<?php

namespace App\Console\Commands;

use App\Models\Inventory\Geolocation\Point;
use Log;
use Cache;
use Illuminate\Console\Command;
use App\Models\User\Location\Geolocation;
use Illuminate\Database\Eloquent\Builder;

class LatLongPrecisionUpdaterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geolocation:precision';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the precision of the records in the geolocation table that have 2 decimal points';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('This command is going to run over all the records in the geolocation table and update the precision of each record that has two decimal points.');

        if(!$this->confirm('This will take a while. Press [ENTER] to begin')) {
            return 0;
        }

        $baseQuery = Geolocation::whereRaw('(round(latitude,0) - round(latitude,2) <> 0)')->orWhereRaw('(round(latitude,0) - round(latitude,2) <> 0)');

        $this->info("Processing {$baseQuery->count()} records...");

        $cacheKey = self::class;

        $exists = Cache::get(self::class);

        $this->process(!!$exists, $baseQuery,  $cacheKey, $exists);
    }

    private function process(bool $continue, Builder $query, string $cacheKey, $position = null)
    {
        $startPos = 0;

        if ($continue) {
            $startPos = intval($position) ?? 0;
        }

        // TODO: Pass the start position to the chunk method so inturrupted processes continue from where they were inturupted

        $query->chunk(500, function($data, $chunkNumber) use ($cacheKey) {
            $this->alert("Processing chunk number '{$chunkNumber}'");

            $data->each(function(Geolocation $item) {
                $latLong = $this->getLongitudeAndLatitude($item->zip);

                // If the method returns null, there was an issue with getting info for that zip. Log it and move on
                if($latLong === null) {
                    return $this->addError($item);
                }

                $item->update([
                    'latitude' => $latLong->latitude,
                    'longitude' => $latLong->longitude
                ]);
            });

            Cache::set($cacheKey, $chunkNumber);
        });
    }

    /**
     * Logs an error encountered processing the specified ZIP code
     *
     * @return void
     */
    protected function addError(Geolocation $item)
    {
        Log::error("[geolocation:precision] Failed to get the lat/long value for {$item->id}");

        $this->error("Failed to get the lat/long value for {$item->id}");
    }

    protected function getGoogleMapsAttributes(string $address)
    {
        return [
            'key' => config('google.maps.api_key'),
            'sensor' => 'false',
            'address' => $address
        ];
    }


    /**
     * Get the latitude and longitude value for a ZIP code from the Geocoding API
     *
     * @return Point|null
     */
    protected function getLongitudeAndLatitude(string $zip) : ?Point {
        $query = http_build_query($this->getGoogleMapsAttributes($zip));

        $url = config('google.maps.url') . "?{$query}";

        $result_string = file_get_contents($url);

        $result = json_decode($result_string, true);

        if (!$result || empty($result['results'])) {
           return null;
        }

        return new Point(
            floatval($result['results'][0]['geometry']['location']['lat']),
            floatval($result['results'][0]['geometry']['location']['lng'])
        );
    }

}