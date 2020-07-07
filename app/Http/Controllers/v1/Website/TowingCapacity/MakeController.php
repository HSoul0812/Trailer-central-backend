<?php

namespace App\Http\Controllers\v1\Website\TowingCapacity;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Website\TowingCapacity\MakesIndexRequest;
use App\Services\Website\TowingCapacity\TowingCapacityService;
use App\Transformers\Website\TowingCapacity\MakeTransformer;
use Dingo\Api\Http\Request;

/**
 * Class MakeController
 * @package App\Http\Controllers\v1\Website\TowingCapacity
 */
class MakeController extends RestfulController
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
     *     path="/api/website/towing-capacity/makes/year/{year}",
     *     description="Retrieve a list of makes",
     *     tags={"Towing capasity makes"},
     *   @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="Year",
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
     * @return \Dingo\Api\Http\Response
     */
    public function index(Request $request, int $year = null)
    {
        $request = new MakesIndexRequest(['year' => $year]);

        if ($request->validate()) {
            return $this->response->collection($this->service->getMakes($year), new MakeTransformer());
        }

        return $this->response->errorBadRequest();
    }
}
