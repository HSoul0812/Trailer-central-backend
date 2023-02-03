<?php

namespace App\Services\User;

use App\Contracts\LoggerServiceInterface;
use App\Repositories\User\GeoLocationRepository;
use Grimzy\LaravelMysqlSpatial\Types\Point;

class GeolocationService implements GeoLocationServiceInterface
{
    /** @var GeoLocationRepository */
    private $repository;

    /** @var LoggerServiceInterface */
    private $loggerService;

    public function __construct(GeoLocationRepository $repository, LoggerServiceInterface $loggerService)
    {
        $this->repository = $repository;
        $this->loggerService = $loggerService;
    }

    public function geoPointFromZipCode(string $zipCode): ?Point
    {
        try {
            $geoLocation = $this->repository->get(['zip' => $zipCode]);

            return $geoLocation ? new Point($geoLocation->latitude, $geoLocation->longitude) : null;
        } catch (\Exception $exception) {
            $this->loggerService->info(sprintf('[%s:%s] %s doesnt exists', __CLASS__, __METHOD__, $zipCode));

            return null;
        }
    }
}
