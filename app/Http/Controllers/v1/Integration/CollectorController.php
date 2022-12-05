<?php

namespace App\Http\Controllers\v1\Integration;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulController;
use App\Http\Requests\Integration\GetCollectorRequest;
use App\Http\Requests\Integration\UpdateCollectorRequest;
use App\Repositories\Integration\CollectorRepositoryInterface;
use App\Transformers\Integration\CollectorTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

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
        $this->middleware('setDealerIdOnRequest')->only(['update']);
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
    public function index(Request $request)
    {
        $request = new GetCollectorRequest($request->all());

        if ($request->validate()) {
            return $this->response->collection($this->collectorRepository->withRequest($request)->getAll([]), new CollectorTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response|null
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function update(int $id, Request $request): Response
    {
        $request = new UpdateCollectorRequest(array_merge($request->all(), ['id' => $id]));

        if ($request->validate()) {
            $item = $this->collectorRepository->update($request->all());
            return $this->response->item($item, new CollectorTransformer());
        }

        return $this->response->errorBadRequest();
    }
}
