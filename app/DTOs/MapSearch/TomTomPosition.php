<?php

declare(strict_types=1);

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class TomTomPosition
{
    public float $lat;
    public float $lon;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->lat = $data['lat'];
        $obj->lon = $data['lon'];

        return $obj;
    }
}
