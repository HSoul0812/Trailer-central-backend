<?php

namespace App\Http\Controllers\v1\Website\TowingCapacity;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Website\TowingCapacity\ModelRequest;
use App\Http\Requests\Website\TowingCapacity\VehiclesRequest;
use App\Services\Website\TowingCapacity\TowingCapacityService;
use App\Transformers\Website\TowingCapacity\ModelTransformer;
use App\Transformers\Website\TowingCapacity\VehiclesTransformer;
use App\Transformers\Website\TowingCapacity\YearsTransformer;
use Dingo\Api\Http\Request;

class VehicleController extends RestfulController
{
    /**
     * @var TowingCapacityService
     */
    private $service;

    /**
     * MakeController constructor.
     * @param TowingCapacityService $service
     */
    public function __construct(TowingCapacityService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/website/towing-capacity/models/year/{year}/make/{makeId}",
     *     description="Retrieve a list of models",
     *     tags={"Towing capasity models"},
     *   @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="Year",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="makeId",
     *         in="query",
     *         description="makeId",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of makes",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param int $year
     * @param int $makeId
     * @return \Dingo\Api\Http\Response
     */
    public function getModels(int $year, int $makeId)
    {
        $request = new ModelRequest([
            'year' => $year,
            'makeId' => $makeId,
        ]);

        if ($request->validate()) {
            return $this->response->collection($this->service->getVehicleModels($year, $makeId), new ModelTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/website/towing-capacity/vehicles/year/{year}/make/{makeId}?model={model}",
     *     description="Retrieve a list of vehicles",
     *     tags={"Towing capasity vehicles"},
     *   @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="Year",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="makeId",
     *         in="query",
     *         description="makeId",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="model",
     *         in="query",
     *         description="model",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of makes",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param Request $request
     * @param int $year
     * @param int $makeId
     * @return \Dingo\Api\Http\Response
     */
    public function getVehicles(Request $request, int $year, int $makeId)
    {
        $model = $request->query('model');

        $request = new VehiclesRequest([
            'year' => $year,
            'makeId' => $makeId,
            'model' => $model,
        ]);

        if ($request->validate()) {
            return $this->response->collection($this->service->getVehicles($year, $makeId, $model), new VehiclesTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/website/towing-capacity/vehicles/years",
     *     description="Retrieve a list of years of vehicles",
     *     tags={"Towing capasity years"},
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of makes",
     *         @OA\JsonContent()
     *     ),
     * )
     *
     * @return \Dingo\Api\Http\Response
     */
    public function getYears()
    {
        return $this->response->collection($this->service->getYears(), new YearsTransformer());
    }
}
