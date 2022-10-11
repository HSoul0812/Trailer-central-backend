<?php

namespace App\Services\ElasticSearch\Inventory\Geolocation;

interface GeolocationInterface
{
    public function lat(): float;

    public function lon(): float;

    public function filterOver(): ?string;

    public function toPoint();
}
