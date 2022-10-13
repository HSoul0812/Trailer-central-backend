<?php

namespace App\Services\ElasticSearch\Inventory\Parameters\Geolocation;

use App\Exceptions\ElasticSearch\InvalidRequestException;
use App\Models\Inventory\Geolocation\Point;

class Geolocation implements GeolocationInterface
{
    private const DELIMITER_OPTION = ';';
    private const DELIMITER_VALUE = ':';

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
     * @param  string  $string  a string like `lat:lon`
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

            $range = $parts->get('range');
            $units = $parts->get('units');
            $scattered = $parts->get('scattered');

            if ($scattered) {
                return new ScatteredGeolocation($lat, $lon, $scattered);
            }

            if ($range) {
                return new GeolocationRange($lat, $lon, $range, $units ?? GeolocationRange::UNITS_MILES);
            }

            return new Geolocation($lat, $lon);
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

    public function toPoint(): Point
    {
        return new Point($this->lat, $this->lon);
    }
}
