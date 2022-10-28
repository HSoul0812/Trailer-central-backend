<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User\Location\Geolocation;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Inventory\Geolocation\Point;

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

        $baseQuery = Geolocation::whereRaw('(round(latitude,0) - round(latitude,2) <> 0)')
            ->orWhereRaw('(round(longitude,0) - round(longitude,2) <> 0)');

        $this->info("Processing {$baseQuery->count()} records...");

        $this->process($baseQuery);
    }

    private function process(Builder $query)
    {
        $query->chunk(500, function($data, $chunkNumber) {
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
        });
    }

    /**
     * Logs an error encountered processing the specified ZIP code
     *
     * @return void
     */
    protected function addError(Geolocation $item)
    {
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

        $latitude = floatval($result['results'][0]['geometry']['location']['lat']);
        $longitude = floatval($result['results'][0]['geometry']['location']['lng']);

        if($this->getDecimalPlacesCount($latitude) <= 4 || $this->getDecimalPlacesCount($latitude) <= 4) {
            $point = $this->getLongitudeAndLatitudeFromBoundsBox($result['results'][0]['bounds']);

            $latitude = $point->latitude;
            $longitude = $point->longitude;
        }

        return new Point($latitude, $longitude);
    }

    /**
     * Get the central point of a bounds box
     *
     * @param array $bounds
     * @return Point
     */
    private function getLongitudeAndLatitudeFromBoundsBox(array $bounds) : Point
    {
        $latitude = (floatval($bounds['northeast']['lat']) + floatval($bounds['southwest']['lat'])) / 2;
        $longitude = (floatval($bounds['northeast']['lng']) + floatval($bounds['southwest']['lng'])) / 2;

        return new Point($latitude, $longitude);
    }

    /**
     * Hacky, dirty way to get the places after the decimal point
     *
     * @param float $number The number to check decimal places for
     *
     * @return int
     */
    private function getDecimalPlacesCount(float $number) : int
    {
        $parts = explode('.', $number);

        return count(str_split($parts[1]));
    }

}
