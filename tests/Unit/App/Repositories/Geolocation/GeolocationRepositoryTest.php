<?php

namespace Tests\Unit\App\Repositories\Geolocation;

use App\Repositories\Geolocation\GeolocationRepository;
use App\Repositories\Geolocation\GeolocationRepositoryInterface;
use Tests\Common\TestCase;

class GeolocationRepositoryTest extends TestCase
{
    public function testGet() {
        $repository = $this->getConcreteRepository();
        $location = $repository->get(['city' => 'AIBONITO', 'state' => 'PR']);
        $this->assertEquals('00705', $location->zip);
    }

    private function getConcreteRepository():GeolocationRepository {
        return app()->make(GeolocationRepositoryInterface::class);
    }
}
