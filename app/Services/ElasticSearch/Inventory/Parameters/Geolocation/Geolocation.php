<?php

namespace App\Services\ElasticSearch\Inventory\Parameters\Geolocation;

use App\Models\Inventory\Geolocation\Point;

class Geolocation implements GeolocationInterface
{
    /** @var float */
    private $lat;

    /** @var float */
    private $lon;

    protected function __construct(float $lat, float $lon)
    {
        $this->lat = $lat;
        $this->lon = $lon;
    }

    /**
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): self
    {
        $lat = $data['lat'];
        $lon = $data['lon'];
        
        if ($grouping = $data['grouping']) {
            return new ScatteredGeolocation($lat, $lon, $grouping);
        }

        if ($range = $data['range']) {
            return new GeolocationRange($lat, $lon, $range, $data['units'] ?? GeolocationRange::UNITS_MILES);
        }

        return new Geolocation($lat, $lon);
    }

    public function lat(): float
    {
        return $this->lat;
    }

    public function lon(): float
    {
        return $this->lon;
    }

    public function toPoint(): Point
    {
        return new Point($this->lat, $this->lon);
    }
}
