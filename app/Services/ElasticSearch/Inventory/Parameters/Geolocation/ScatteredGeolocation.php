<?php

namespace App\Services\ElasticSearch\Inventory\Parameters\Geolocation;

class ScatteredGeolocation extends Geolocation
{
    /** @var int */
    private $grouping;

    public function __construct(float $lat, float $lon, int $grouping, ?string $filterOver = null)
    {
        $this->grouping = $grouping;

        parent::__construct($lat, $lon, $filterOver);
    }

    public function grouping(): int
    {
        return $this->grouping;
    }
}
