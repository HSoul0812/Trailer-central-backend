<?php

declare(strict_types=1);

namespace App\Services\MapSearchService;

use League\Fractal\TransformerAbstract;

interface MapSearchServiceInterface
{
    /**
     * @param string $searchText
     * @return object
     */
    public function autocomplete(string $searchText): object;

    /**
     * @param string $address
     * @return object
     */
    public function geocode(string $address): object;

    /**
     * @param float $lat
     * @param float $lng
     * @return object
     */
    public function reverse(float $lat, float $lng): object;

    public function getTransformer(): TransformerAbstract;
}
