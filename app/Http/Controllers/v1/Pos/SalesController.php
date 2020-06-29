<?php


namespace App\Http\Controllers\v1\Pos;


use App\Utilities\JsonApi\QueryBuilder;
use App\Http\Controllers\RestfulControllerV2;
use App\Models\Pos\Sale;
use Dingo\Api\Http\Request;
use OpenApi\Annotations as OA;

/**
 * Class SalesController
 *
 * Controller for POS sales API
 *
 * @package App\Http\Controllers\v1\Pos
 */
class SalesController extends RestfulControllerV2
{
    /**
     * List/browse all pos-based queries
     *
     * @param Request $request
     * @param \App\Utilities\JsonApi\QueryBuilder $builder
     *
     * @OA\Get(
     *     path="/pos/sales"
     * )
     */
    public function index(Request $request, QueryBuilder $builder)
    {
        // instantiate a model query builder
        $eloquent = Sale::query();

        // do other stuff with the model here

        // build the query
        $query = $builder
            ->withRequest($request)
            ->withQuery($eloquent)
            ->build();

        return $query->get();
    }

    /**
     * Return a single POS sale record
     * @param $id
     *
     * @param Request $request
     * @param \App\Utilities\JsonApi\QueryBuilder $builder
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path="/pos/sale/{id}",
     *     description="Get a POS sales record",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="POS Sales ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     * )
     */
    public function show($id, Request $request, QueryBuilder $builder)
    {
        // instantiate a model query builder
        $eloquent = Sale::query();

        // build the query
        $query = $builder
            ->withRequest($request)
            ->withQuery($eloquent)
            ->build();

        return response()->json(['data' => $query->findOrFail($id)]);
    }
}
