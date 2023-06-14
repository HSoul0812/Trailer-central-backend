<?php

namespace Tests\Integration\Services\Location;

use App\Services\User\GeoLocationServiceInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Tests\TestCase;

/**
 * @group DW
 * @group DW_GEOLOCATION
 */
class GeolocationServiceTest extends TestCase
{
    public function testItThrowsAnExceptionIfTheParamsToSearchAreNotSupported()
    {
        $this->expectException(BadRequestHttpException::class);
        /** @var GeoLocationServiceInterface $service */
        $service = $this->app->make(GeoLocationServiceInterface::class);

        $service->search(['some' => 111]);
    }
}
