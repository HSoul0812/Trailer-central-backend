<?php

declare(strict_types=1);

namespace App\Models\Inventory\Geolocation;

use App\Traits\WithGetter;

/**
 * @property-read float $latitude
 * @property-read float $longitude
 */
class Point
{
    use WithGetter;

    /** @var float */
    private $latitude;

    /** @var float */
    private $longitude;

    public function __construct(float $latitude, float $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }
}
