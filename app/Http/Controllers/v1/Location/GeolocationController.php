<?php

namespace App\Http\Controllers\v1\Location;

use App\Http\Controllers\RestfulControllerV2;
use App\Services\User\GeoLocationServiceInterface;
use App\Transformers\Location\GeolocationTransformer;
use Dingo\Api\Http\Response;
use Illuminate\Http\Request;

class GeolocationController extends RestfulControllerV2
{
    /**
     * @var GeoLocationServiceInterface
     */
    private $geoLocationService;

    public function __construct(GeoLocationServiceInterface $geoLocationService)
    {
        $this->geoLocationService = $geoLocationService;
    }

    public function search(Request $request): Response
    {
        $locations = $this->geoLocationService->search($request->query());

        return $this->response->collection($locations, new GeolocationTransformer());
    }
}
