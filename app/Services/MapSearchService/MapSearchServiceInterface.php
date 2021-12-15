<?php

declare(strict_types=1);

namespace App\Services\MapSearchService;

use League\Fractal\TransformerAbstract;

interface MapSearchServiceInterface
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
    public function reverse(string $lat, string $lng);

    /**
     * @return TransformerAbstract
     */
    public function getTransformer(): TransformerAbstract;
}
