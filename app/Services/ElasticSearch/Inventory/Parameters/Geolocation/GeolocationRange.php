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

    /** @var bool */
    private $appendToPostQuery;

    public function __construct(
        float  $lat,
        float  $lon,
        float  $range,
        string $units = self::UNITS_MILES,
        bool   $appendToPostQuery = false
    )
    {
        $this->range = $range;
        $this->units = $units;
        $this->appendToPostQuery = $appendToPostQuery;

        parent::__construct($lat, $lon);
    }

    public function range(): ?float
    {
        return $this->range;
    }

    public function units(): string
    {
        return $this->units;
    }

    public function appendToPostQuery(): bool
    {
        return $this->appendToPostQuery;
    }
}
