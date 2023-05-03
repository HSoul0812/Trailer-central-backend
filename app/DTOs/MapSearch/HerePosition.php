<?php

declare(strict_types=1);

namespace App\DTOs\MapSearch;

use JetBrains\PhpStorm\Pure;

class HerePosition
{
    public float $lat;
    public float $lng;

    #[Pure]
    public static function fromData(array $data): self
    {
        $obj = new self();
        $obj->lat = $data['lat'];
        $obj->lng = $data['lng'];

        return $obj;
    }
}
