<?php

namespace App\DTOs\Inventory;

use Illuminate\Contracts\Support\Arrayable;
use JetBrains\PhpStorm\Pure;

class Geolocation implements Arrayable
{
    use \App\DTOs\Arrayable;

    public string $lat;
    public string $lon;

    #[Pure] public static function fromData(array $geo): self
    {
        $obj = new self();
        $obj->lat = $geo['lat'];
        $obj->lon = $geo['lon'];
        return $obj;
    }
}
