<?php


namespace App\Services\GeocodeService;


class HereGeocodeService implements GeocodeServiceInterface
{
    const AUTOCOMPLETE_API_URL = 'https://autocomplete.search.hereapi.com/v1/autocomplete';
    const GEOCODE_API_URL = 'https://geocode.search.hereapi.com/v1/geocode';
    const REVERSE_API_URL = 'https://revgeocode.search.hereapi.com/v1/revgeocode';

    public function autocomplete(string $searchText)
    {
        $query = http_build_query(['q' => $searchText]);
    }

    public function geocode(string $address)
    {

    }

    public function reverse(float $lat, float $lng)
    {

    }
}
