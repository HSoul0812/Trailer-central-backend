<?php

namespace App\Services\ElasticSearch\Inventory\Parameters\Geolocation;

interface GeolocationInterface
{
    public function lat(): float;

    public function lon(): float;

    public function toPoint();
}
