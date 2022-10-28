<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User\DealerLocation;
use App\Models\User\Location\Geolocation;
use Illuminate\Database\Eloquent\Builder;

class LatLongDealerPrecisionUpdaterCommand extends Command
{
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
    protected $description = 'Update the precision of the records in the dealer locations table that have 2 decimal points';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line('This command is going to run over all the records in the dealer locations table and update the precision of each record that has two decimal points.');

        if(!$this->confirm('This will take a while. Press [ENTER] to begin')) {
            return 0;
        }

        $baseQuery = DealerLocation::whereRaw('(round(latitude,0) - round(latitude,2) <> 0)')
                        ->orWhereRaw('(round(longitude,0) - round(longitude,2) <> 0)')
                        ->orWhereNull('latitude')
                        ->orWhereNull('longitude');

        $this->info("Processing {$baseQuery->count()} records...");

        $this->process($baseQuery);
    }

    /**
     * Process the records
     *
     * @return void
     */
    private function process(Builder $query)
    {
        $query->chunk(500, function($data, $chunkNumber) {
            $this->alert("Processing chunk number '{$chunkNumber}'");

            $data->each(function(DealerLocation $location) {
                $searchQuery = "";
                $isCanadianPostcode = null;

                if($location->postalcode) {
                    $searchQuery = $location->postalcode;

                    $isCanadianPostcode = !!preg_match('/^([A-Za-z]\d[A-Za-z][-]?\d[A-Za-z]\d)/i', $searchQuery);
                } else {
                    $searchQuery = "{$location->address}, {$location->city}, {$location->county}, {$location->region}";
                }

                $latLong = $this->getLongitudeAndLatitude($searchQuery, $isCanadianPostcode);

                // If the method returns null, there was an issue with getting info for that zip. Log it and move on
                if($latLong === null) {
                    return $this->addError($location, $searchQuery);
                }

                // Update the record in the DB
                $location->update([
                    'latitude' => $latLong->latitude,
                    'longitude' => $latLong->longitude
                ]);

                $this->findCreateOrUpdateGeolocationRecord($location, $latLong);
            });
        });
    }

    /**
     * Finds a location in the Geolocation table, if it exists, update it with the new lat long, else, insert a new record
     */
    protected function findCreateOrUpdateGeolocationRecord(DealerLocation $dealer, object $latLong)
    {
        // Get a record for a corresponding Geolocation record for a given ZIP code
        $record = Geolocation::where('zip', $latLong->zip ?? $dealer->postalcode)->first();

        if(!$record) {
            return Geolocation::create([
                'zip' => $latLong->zip,
                'city' => $latLong->city,
                'state' => $latLong->state,
                'country' => $latLong->country,
                'longitude' => $latLong->longitude,
                'latitude' => $latLong->latitude
            ]);
        }

        $record->update([
            'longitude' => $latLong->longitude,
            'latitude' => $latLong->latitude
        ]);
    }

    /**
     * Logs an error encountered processing the specified record
     *
     * @return void
     */
    protected function addError(DealerLocation $location, string $query)
    {
        $this->error("Failed to get the lat/long value for {$location->dealer_location_id} q. {$query}");
    }


    protected function getGoogleMapsAttributes(string $address, $isCanadianPostcode = null)
    {
        $country = '';

        // Add a component restriction for a ZIP code
        if ($isCanadianPostcode !== null) {
            $country = ($isCanadianPostcode) ? ':CA' : ':US';
        }

        return [
            'key' => config('google.maps.api_key'),
            'sensor' => 'false',
            'address' => $address,
            'components' => implode(',', [
                'locality',
                'administrative_area_level_1',
                'postal_code',
                "country{$country}"
            ])
        ];
    }

    /**
     * Get the latitude and longitude value for a ZIP code / Address from the Geocoding API
     *
     * @return {longitude: float, latitude: float}|null
     */
    protected function getLongitudeAndLatitude(string $address, $isCanadianPostcode) : ?object {
        $query = http_build_query($this->getGoogleMapsAttributes($address, $isCanadianPostcode));

        $url = config('google.maps.url') . "?{$query}";

        $result_string = file_get_contents($url);

        $result = json_decode($result_string, true);

        if (!$result || empty($result['results'])) {
           return null;
        }

        try {
            return (object)[
                'latitude' => floatval($result['results'][0]['geometry']['location']['lat']),
                'longitude' => floatval($result['results'][0]['geometry']['location']['lng']),
                'city' =>  $result['results'][0]['address_components'][1]['short_name'],
                'state' => $result['results'][0]['address_components'][3]['short_name'],
                'zip' => $result['results'][0]['address_components'][0]['short_name'],
                'country' => $this->mapCountryIsoShortToLongCode($result['results'][0]['address_components'][4]['short_name'])
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the short name (formatted to conform to our DB) for a country
     *
     * @return string
     */
    protected function mapCountryIsoShortToLongCode($shortCode): string
    {
        $isoCodes = ['US' => 'USA', 'CA' => 'CA'];

        return $isoCodes[$shortCode] ?? 'USA';
    }

}
