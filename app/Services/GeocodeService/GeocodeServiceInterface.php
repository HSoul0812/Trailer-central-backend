<?php


namespace App\Services\GeocodeService;


interface GeocodeServiceInterface
{
    /**
     * @param string $searchText
     * @return mixed
     */
    public function autocomplete(string $searchText);

    /**
     * @param string $address
     * @return mixed
     */
    public function geocode(string $address);

    /**
     * @param float $lat
     * @param float $lng
     * @return mixed
     */
    public function reverse(float $lat, float $lng);
}
