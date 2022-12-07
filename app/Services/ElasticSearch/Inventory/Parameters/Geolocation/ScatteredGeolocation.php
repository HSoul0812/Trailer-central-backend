<?php

namespace App\Services\ElasticSearch\Inventory\Parameters\Geolocation;

class ScatteredGeolocation extends Geolocation
{
    /** @var int */
    private $grouping;

    public function __construct(float $lat, float $lon, int $grouping)
    {
        $this->grouping = $grouping;

        parent::__construct($lat, $lon);
    }

    public function grouping(): int
    {
        return $this->grouping;
    }
}
