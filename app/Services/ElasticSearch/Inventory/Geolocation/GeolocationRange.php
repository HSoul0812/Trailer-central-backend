<?php

namespace App\Services\ElasticSearch\Inventory\Geolocation;

class GeolocationRange extends Geolocation
{
    public const UNITS_MILES = 'mi';
    public const UNITS_KILOMETERS = 'km';

    /** @var float */
    private $range;

    /** @var string */
    private $units;

    public function __construct(float   $lat,
                                float   $lon,
                                float   $range,
                                string  $units = self::UNITS_MILES,
                                ?string $filterOver = null)
    {
        $this->range = $range;
        $this->units = $units;

        parent::__construct($lat, $lon, $filterOver);
    }

    public function range(): float
    {
        return $this->range;
    }

    public function units(): string
    {
        return $this->units;
    }
}
