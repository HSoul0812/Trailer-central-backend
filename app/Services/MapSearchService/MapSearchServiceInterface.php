<?php

declare(strict_types=1);

namespace App\Services\MapSearchService;

use League\Fractal\TransformerAbstract;

interface MapSearchServiceInterface
{
    public function autocomplete(string $searchText): object;

    public function geocode(string $address): object;

    public function reverse(float $lat, float $lng): object;

    public function getTransformer(string $class): TransformerAbstract;
}
