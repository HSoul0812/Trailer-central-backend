<?php

namespace Tests\Unit\App\Services\IpInfo;

use App\Services\IpInfo\IpInfoService;
use GeoIp2\Exception\AddressNotFoundException;
use JetBrains\PhpStorm\Pure;
use Tests\Common\TestCase;

class IpInfoServiceTest extends TestCase
{
    private IpInfoService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->getConcreteService();
    }

    /**
     * @dataProvider cityIPProvider
     */
    public function testGetCityByIP($ip, $country, $state, $city)
    {
        $location = $this->service->city($ip);
        $this->assertEquals($location->countryISO, $country);
        $this->assertEquals($location->stateISO, $state);
        $this->assertEquals($location->city, $city);
    }

    public function cityIPProvider(): array
    {
        return [
            'us city1' => ['206.71.50.230', 'US', 'NY', 'Brooklyn'],
            'us city2' => ['65.49.22.66', 'US', 'CA', 'Livermore'],
            'canada city1' => ['192.206.151.131', 'CA', 'ON', 'Toronto'],
        ];
    }

    public function testGetCityByLocalIP()
    {
        $this->expectException(AddressNotFoundException::class);
        $this->service->city('172.17.0.1');
    }

    #[Pure]
 private function getConcreteService(): IpInfoService
 {
     return new IpInfoService();
 }
}
