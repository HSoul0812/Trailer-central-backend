<?php

namespace App\Http\Controllers\v1\Feed\Factory;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Showroom\ShowroomRepositoryInterface;
use App\Transformers\Feed\Factory\ShowroomTransformer;
use App\Http\Requests\Feed\Factory\GetShowroomRequest;

/**
 * @author Marcel
 */
class ShowroomController extends RestfulController
{

    protected $showroom;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ShowroomRepositoryInterface $showroom)
    {
        $this->showroom = $showroom;
    }

    /**
     * @OA\Get(
     *     path="/api/feed/factory/showroom",
     *     description="Retrieve a list of showroom",
     *     tags={"Feed"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         in="query",
     *         description="Search term",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="manufacturer",
     *         in="query",
     *         description="Manufacturer",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of showroom",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $request = new GetShowroomRequest($request->all());

        if ($request->validate()) {
            $response = $this->response->paginator($this->showroom->getAll($request->all()), app()->make(ShowroomTransformer::class));
        }

        return $response;

        return $this->response->errorBadRequest();
    }

}
