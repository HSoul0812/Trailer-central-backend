<?php

namespace App\DTOs\Inventory;

use App\Traits\TypedPropertyTrait;
use Illuminate\Contracts\Support\Arrayable;
use JetBrains\PhpStorm\Pure;

class TcEsGeolocation implements Arrayable
{
    use \App\DTOs\Arrayable;
    use TypedPropertyTrait;

    public float $lat;
    public float $lon;

    #[Pure]
    public static function fromData(array $geo): self
    {
        $obj = new self();
        $obj->setTypedProperty('lat', $geo['lat']);
        $obj->setTypedProperty('lon', $geo['lon']);

        return $obj;
    }
}
