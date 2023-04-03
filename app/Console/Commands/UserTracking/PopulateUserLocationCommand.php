<?php

namespace App\Console\Commands\UserTracking;

use App\Domains\Commands\Traits\PrependsOutput;
use App\Domains\Commands\Traits\PrependsTimestamp;
use App\Models\UserTracking;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use GeoIp2\Model\City;
use Illuminate\Console\Command;
use MaxMind\Db\Reader\InvalidDatabaseException;

class PopulateUserLocationCommand extends Command
{
    use PrependsOutput, PrependsTimestamp;

    protected $signature = 'user-tracking:populate-location';

    protected $description = 'Populate user location from the IP address';

    protected Reader $reader;

    /**
     * @throws InvalidDatabaseException
     */
    public function handle(): int
    {
        $this->info(sprintf("%s command started...", $this->signature));

        $this->reader = new Reader(storage_path('app/maxmind/GeoLite2-City.mmdb'));

        UserTracking::query()
            ->distinct()
            ->where('location_processed', false)
            ->get(['ip_address'])
            ->pluck('ip_address')
            ->each(fn(string $ipAddress) => $this->populateLocationForIpAddress($ipAddress));

        $this->info(sprintf("%s command finished!", $this->signature));

        return 0;
    }

    private function populateLocationForIpAddress(string $ipAddress): void
    {
        try {
            $record = $this->reader->city($ipAddress);

            [$city, $state, $country] = $this->getCityAndState($record);

            UserTracking::where('ip_address', $ipAddress)->update([
                'location_processed' => true,
                'city' => $city,
                'state' => $state,
                'country' => $country,
            ]);

            $this->info("Location processed for IP address $ipAddress");
        } catch (AddressNotFoundException) {
            UserTracking::where('ip_address', $ipAddress)->update([
                'location_processed' => true,
            ]);

            $this->error("Address not found for IP address $ipAddress");
        } catch (InvalidDatabaseException $e) {
            $this->error("Invalid database data: {$e->getMessage()}");
        }
    }

    private function getCityAndState(City $record): array
    {
        $city = $record->city->name;
        $state = $record->mostSpecificSubdivision->isoCode;
        $country = $record->country->isoCode;

        return [$city, $state, $country];
    }
}
