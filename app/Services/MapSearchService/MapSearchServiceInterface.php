<?php

declare(strict_types=1);

namespace App\Services\MapSearchService;

use League\Fractal\TransformerAbstract;

interface MapSearchServiceInterface
{
    /**
     * @return mixed
     */
    public function autocomplete(string $searchText): object;

    /**
     * @return mixed
     */
    public function geocode(string $address): object;

    /**
     * @return mixed
     */
    public function reverse(float $lat, float $lng): object;

    public function getTransformer(): TransformerAbstract;
}
