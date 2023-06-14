<?php

namespace App\Services\User;

use App\Contracts\LoggerServiceInterface;
use App\Repositories\User\GeoLocationRepository;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GeolocationService implements GeoLocationServiceInterface
{
    /** @var GeoLocationRepository */
    private $repository;

    /** @var LoggerServiceInterface */
    private $loggerService;

    const SEARCH_FIELDS = ['zip', 'city', 'state'];

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

    /**
     * @throws \Throwable
     */
    public function search(array $params): Collection
    {
        throw_if(
            empty($params) || !empty(array_diff(array_keys($params), self::SEARCH_FIELDS)),
            new BadRequestHttpException()
        );

        return $this->repository->search($params);
    }
}
