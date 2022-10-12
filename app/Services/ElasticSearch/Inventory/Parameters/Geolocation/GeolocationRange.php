<?php

namespace App\Services\ElasticSearch\Inventory\Parameters\Geolocation;

class GeolocationRange extends Geolocation
{
    public const UNITS_MILES = 'mi';
    public const UNITS_KILOMETERS = 'km';

    public const SORT_ASC = 'asc';

    /** @var int */
    private $range;

    /** @var string */
    private $units;

    /** @var string */
    private $sorting;

    public function __construct(float   $lat,
                                float   $lon,
                                int   $range,
                                string  $units = self::UNITS_MILES,
                                string  $sorting = self::SORT_ASC,
                                ?string $filterOver = null)
    {
        $this->range = $range;
        $this->units = $units;
        $this->sorting = $sorting;

        parent::__construct($lat, $lon, $filterOver);
    }

    public function range(): ?int
    {
        return $this->range;
    }

    public function units(): string
    {
        return $this->units;
    }

    public function sort(): string
    {
        return $this->sorting;
    }
}
