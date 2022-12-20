<?php

namespace App\Services\ElasticSearch\Inventory\Parameters\Geolocation;

class GeolocationRange extends Geolocation
{
    /** @var string */
    public const GROUPING_RANGE = 'range';

    /** @var string */
    public const GROUPING_UNITS = 'units';

    /** @var string */
    public const UNITS_MILES = 'mi';

    /** @var string */
    public const UNITS_KILOMETERS = 'km';

    /** @var string */
    public const SORT_ASC = 'asc';

    /** @var int */
    private $range;

    /** @var string */
    private $units;

    /** @var string */
    private $sorting;

    public function __construct(
        float  $lat,
        float  $lon,
        int    $range,
        string $units = self::UNITS_MILES
    )
    {
        $this->range = $range;
        $this->units = $units;

        parent::__construct($lat, $lon);
    }

    public function range(): ?int
    {
        return $this->range;
    }

    public function units(): string
    {
        return $this->units;
    }
}
