<?php

namespace App\DTOs\IpInfo;

use Illuminate\Contracts\Support\Arrayable;

class City implements Arrayable
{
    use \App\DTOs\Arrayable;

    public ?string $city;
    public ?string $postalcode;
    public ?string $state;
    public ?string $stateISO;
    public ?string $latitude;
    public ?string $longitude;
    public string $country;
    public string $countryISO;
    public string $network;

    public static function fromGeoIP2City(\GeoIp2\Model\City $city)
    {
        $cityData = new self();
        $cityData->city = $city->city->name;
        $cityData->postalcode = $city->postal->code;
        $cityData->state = $city->mostSpecificSubdivision->name;
        $cityData->stateISO = $city->mostSpecificSubdivision->isoCode;
        $cityData->latitude = $city->location->latitude;
        $cityData->longitude = $city->location->longitude;
        $cityData->country = $city->country->name;
        $cityData->countryISO = $city->country->isoCode;
        $cityData->network = $city->traits->network;

        return $cityData;
    }
}
