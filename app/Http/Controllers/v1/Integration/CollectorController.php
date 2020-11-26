<?php

namespace App\Http\Controllers\v1\Integration;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Integration\GetCollectorRequest;
use App\Repositories\Integration\CollectorRepositoryInterface;
use App\Transformers\Integration\CollectorTransformer;
use Dingo\Api\Http\Request;

/**
 * Class CollectorController
 * @package App\Http\Controllers\v1\Integration
 */
class CollectorController extends RestfulController
{
    /**
     * @var CollectorRepositoryInterface
     */
    private $collectorRepository;

    /**
     * CollectorController constructor.
     * @param CollectorRepositoryInterface $collectorRepository
     */
    public function __construct(CollectorRepositoryInterface $collectorRepository)
    {
        $this->collectorRepository = $collectorRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/integration/collectors",
     *     description="Retrieve a list of collectors",

     *     tags={"Collectors"},
     *     @OA\Parameter(
     *         name="active",
     *         in="query",
     *         description="Is Active",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of collectors",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request) {
        $request = new GetCollectorRequest($request->all());

        if ($request->validate()) {
            return $this->response->collection($this->collectorRepository->withRequest($request)->getAll([]), new CollectorTransformer());
        }

        return $this->response->errorBadRequest();

    }
}
