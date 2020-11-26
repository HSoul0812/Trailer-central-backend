<?php


namespace App\Http\Controllers\v1\Integration;

use App\Http\Controllers\RestfulController;
use App\Repositories\Integration\CollectorFieldsRepositoryInterface;
use App\Transformers\Integration\CollectorFieldsTransformer;
use Dingo\Api\Http\Request;

/**
 * Class CollectorFieldsController
 * @package App\Http\Controllers\v1\Integration
 */
class CollectorFieldsController extends RestfulController
{
    /**
     * @var CollectorFieldsRepositoryInterface
     */
    private $collectorFieldsRepository;

    /**
     * CollectorFieldsController constructor.
     * @param CollectorFieldsRepositoryInterface $collectorFieldsRepository
     */
    public function __construct(CollectorFieldsRepositoryInterface $collectorFieldsRepository)
    {
        $this->collectorFieldsRepository = $collectorFieldsRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/integration/collector/fields",
     *     description="Retrieve a list of collector fields",

     *     tags={"Collector Fields"},
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of collector fields",
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
        return $this->response->collection($this->collectorFieldsRepository->withRequest($request)->getAll([]), new CollectorFieldsTransformer());
    }
}
