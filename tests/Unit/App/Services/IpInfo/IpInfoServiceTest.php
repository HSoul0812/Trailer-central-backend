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
     * @dataProvider cityIpProvider
     */
    public function testGetCityByIp($ip, $country, $state, $city)
    {
        $location = $this->service->city($ip);
        $this->assertEquals($location->countryISO, $country);
        $this->assertEquals($location->stateISO, $state);
        $this->assertEquals($location->city, $city);
    }

    public function cityIpProvider(): array
    {
        return [
            'US city 1' => ['172.58.229.54', 'US', 'NY', 'Brooklyn'],
            'US city 2' => ['128.115.190.36', 'US', 'CA', 'Livermore'],
            'Canada city 1' => ['64.56.236.190', 'CA', 'ON', 'Brampton'],
        ];
    }

    public function testGetCityByLocalIp()
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
