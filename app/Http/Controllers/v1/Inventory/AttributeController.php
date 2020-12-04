<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulController;
use App\Repositories\Inventory\AttributeRepositoryInterface;
use App\Transformers\Inventory\AttributeTransformer;
use Dingo\Api\Http\Request;

/**
 * Class AttributesController
 * @package App\Http\Controllers\v1\Inventory
 */
class AttributeController extends RestfulController
{
    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * AttributesController constructor.
     * @param AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/attributes",
     *     description="Retrieve a list of inventory attributes",

     *     tags={"Inventory Attributes"},
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of inventory attributes",
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
        return $this->response->collection($this->attributeRepository->getAll($request->all()), new AttributeTransformer());
    }
}
