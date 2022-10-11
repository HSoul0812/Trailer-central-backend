<?php

namespace App\Services\ElasticSearch\Inventory\Geolocation;

use App\Exceptions\ElasticSearch\InvalidRequestException;
use App\Models\Inventory\Geolocation\Point;

class Geolocation implements GeolocationInterface
{
    private const DELIMITER_OPTION = ';';
    private const DELIMITER_VALUE = ':';

    /**
     * These constant determines in which filter is appended the filters for Geolocation
     *  - `filter_aggregators` -> fa
     *  - `location_aggregators` -> la
     *  - `selected_location_aggregators` -> sla
     */
    public const FILTERING_OVER_FILTER_AGGREGATORS = 'fa';
    public const FILTERING_OVER_LOCATION_AGGREGATORS = 'la';
    public const FILTERING_OVER_SELECTED_LOCATION_AGGREGATORS = 'sla';
    public const FILTERING_OVER_LOCATION_FILTER_AGGREGATORS = 'fla';
    public const FILTERING_OVER_SELECTED_LOCATION_FILTER_AGGREGATORS = 'slfa';

    /** @var float */
    private $lat;

    /** @var float */
    private $lon;

    /** @var string|null */
    private $filterOver;

    protected function __construct(float $lat, float $lon, ?string $filterOver = null)
    {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->filterOver = $filterOver;

        $this->validateFilterOver($this->filterOver);
    }

    /**
     * @param string $string a string like `lat:lon;filter_over:la`
     * @return static
     */
    public static function fromString(string $string): self
    {
        $parts = collect(array_filter(explode(self::DELIMITER_OPTION, $string)));

        $partsNumber = $parts->count();

        if ($partsNumber === 0) {
            throw new InvalidRequestException("'geolocation' parameter is wrong.");
        }

        [$lat, $lon] = explode(self::DELIMITER_VALUE, $parts->first());

        try {
            $parts = $parts->forget(0)->map(static function (string $part): array {
                [$option, $value] = explode(self::DELIMITER_VALUE, $part);

                return ['option' => $option, 'value' => $value];
            })->pluck('value', 'option');

            $filterOver = $parts->get('filter_over');
            $range = $parts->get('range');
            $units = $parts->get('units');
            $scattered = $parts->get('scattered');
            $sorting = $parts->get('sorting');

            if ($scattered) {
                return new ScatteredGeolocation($lat, $lon, $scattered, $filterOver);
            } elseif ($range) {
                return new GeolocationRange($lat, $lon, $range, $units ?? GeolocationRange::UNITS_MILES, $sorting, $filterOver);
            }

            return new Geolocation($lat, $lon, $filterOver);
        } catch (\Exception $exception) {
            throw new InvalidRequestException("'geolocation' parameter is wrong.");
        }
    }

    public function lat(): float
    {
        return $this->lat;
    }

    public function lon(): float
    {
        return $this->lon;
    }

    public function filterOver(): ?string
    {
        return $this->filterOver;
    }

    /**
     * @param string|null $filterOver
     * @return static a new object with the `filterOver` provided
     */
    public function withFilterOver(?string $filterOver): self
    {
        $that = clone $this;
        $that->filterOver = $filterOver;
        $this->validateFilterOver($that->filterOver);

        return $that;
    }

    /**
     * @param string|null $filterOver
     * @return void
     * @throws \InvalidArgumentException when 'filterOver' has a wrong value
     */
    private function validateFilterOver(?string $filterOver): void
    {
        if ($filterOver && !in_array($filterOver, [
                self::FILTERING_OVER_FILTER_AGGREGATORS,
                self::FILTERING_OVER_LOCATION_AGGREGATORS,
                self::FILTERING_OVER_SELECTED_LOCATION_AGGREGATORS,
                self::FILTERING_OVER_LOCATION_FILTER_AGGREGATORS,
                self::FILTERING_OVER_SELECTED_LOCATION_FILTER_AGGREGATORS
            ], true)) {
            throw new \InvalidArgumentException("Geolocation 'filterOver' has an invalid value.");
        }
    }

    public function toPoint(): Point
    {
        return new Point($this->lat, $this->lon);
    }
}
