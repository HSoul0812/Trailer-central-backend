<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulController;
use App\Repositories\Inventory\StatusRepositoryInterface;
use App\Transformers\Inventory\StatusesTransformer;
use Dingo\Api\Http\Request;

/**
 * Class StatusController
 * @package App\Http\Controllers\v1\Inventory
 */
class StatusController extends RestfulController
{
    /**
     * @var StatusRepositoryInterface
     */
    private $repository;

    /**
     * StatusController constructor.
     * @param StatusRepositoryInterface $repository
     */
    public function __construct(StatusRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/statuses",
     *     description="Retrieve a list of inventory statuses",

     *     tags={"Statuses"},
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of inventory statuses",
     *         @OA\JsonContent()
     *     ),
     * )
     */
    public function index(Request $request)
    {
        return $this->response->collection($this->repository->getAll([]), new StatusesTransformer());
    }
}
