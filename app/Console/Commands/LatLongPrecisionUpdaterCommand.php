<?php

namespace App\Console\Commands;

use Log;
use Cache;
use Illuminate\Console\Command;
use App\Models\User\Location\Geolocation;
use Illuminate\Database\Eloquent\Builder;

class LatLongPrecisionUpdaterCommand extends Command
{
    const GOOGLE_MAPS_ENDPOINT = "https://maps.googleapis.com/maps/api/geocode/json";

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

        // Prompt user to confirm if we should begin the execution of the command
        if(!$this->confirm('This will take a while. Press [ENTER] to begin')) {
            return 0;
        }

        // Base select for all malformed dataa
        $baseQuery = Geolocation::whereRaw('(round(latitude,0) - round(latitude,2) <> 0)')->orWhereRaw('(round(latitude,0) - round(latitude,2) <> 0)');

        // Print number of records to be processed
        $this->info("Processing {$baseQuery->count()} records...");

        // The  key to cache the current position were are at with processing the data
        $cacheKey = self::class;

        // Get if we have a process that hung
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

                // Update the record in the DB
                $item->update([
                    'latitude' => $latLong->getLatitude(),
                    'longitude' => $latLong->getLongitude()
                ]);
            });

            // Remember current chunk number
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
        // Log out the error
        Log::error("[geolocation:precision] Failed to get the lat/long value for {$item->id}");

        // Print an error to the console
        $this->error("Failed to get the lat/long value for {$item->id}");
    }

    protected function getGoogleMapsAttributes(string $address)
    {
        return [
            'key' => env('GOOGLE_MAPS_API_KEY'),
            'sensor' => 'false',
            'address' => $address
        ];
    }


    /**
     * Get the latitude and longitude value for a ZIP code from the Geocoding API
     *
     * @return LatLong|null
     */
    protected function getLongitudeAndLatitude(string $zip) : ?LatLong {
        $query = http_build_query($this->getGoogleMapsAttributes($zip));

        $url = self::GOOGLE_MAPS_ENDPOINT . "?{$query}";

        $result_string = file_get_contents($url);

        $result = json_decode($result_string, true);

        if (!$result || empty($result['results'])) {
           return null;
        }

        return new LatLong(
            floatval($result['results'][0]['geometry']['location']['lat']),
            floatval($result['results'][0]['geometry']['location']['lng'])
        );
    }

}

/**
 * Represents a latitude / longitude pair
 */
class LatLong {
    private $latitude = 0;

    private $longitude = 0;

    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    /**
     * Get the latitude value for the record
     *
     * @return float
     */
    public function getLatitude(): float {
        return $this->latitude;
    }

    /**
     * Get the longitude value for the record
     *
     * @return float
     */
    public function getLongitude(): float {
        return $this->longitude;
    }
}
