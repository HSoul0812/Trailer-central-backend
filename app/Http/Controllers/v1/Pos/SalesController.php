<?php


namespace App\Http\Controllers\v1\Pos;


use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\Pos\SaleRepositoryInterface;
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
     *
     * @param SaleRepositoryInterface $repository
     * @return \Dingo\Api\Http\Response
     * @OA\Get(
     *     path="/pos/sales"
     * )
     */
    public function index(Request $request, SaleRepositoryInterface $repository)
    {
        return $this->response->array([
            'data' => $repository->withRequest($request)->get([])
        ]);
    }

    /**
     * Return a single POS sale record
     * @param $id
     *
     * @param Request $request
     * @param SaleRepositoryInterface $repository
     * @return \Dingo\Api\Http\Response
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
    public function show($id, Request $request, SaleRepositoryInterface $repository)
    {
        return $this->response->array([
            'data' => $repository
                ->withRequest($request) // pass jsonapi request queries onto this queryable repo
                ->find($id)
        ]);
    }


}
