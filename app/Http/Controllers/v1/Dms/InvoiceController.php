<?php


namespace App\Http\Controllers\v1\Dms;


use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\CRM\Invoice\InvoiceRepositoryInterface;
use Dingo\Api\Http\Request;
use OpenApi\Annotations as OA;

/**
 * Class InvoiceController
 * @package App\Http\Controllers\v1\Dms
 */
class InvoiceController extends RestfulControllerV2
{
    /**
     * @param $id
     * @param Request $request
     * @param InvoiceRepositoryInterface $repository
     * @return \Dingo\Api\Http\Response
     *
     * @OA\Get(
     *     path="/invoices",
     *     @OA\Parameter(
     *          name="with",
     *          description="model relations to load"
     *          in="query"
     *     ),
     *     @OA\Parameter(
     *          name="filter",
     *          description="filters to apply, like where clauses"
     *          in="query"
     *     ),
     *     @OA\Parameter(
     *          name="sort",
     *          description="sort specs"
     *          in="query"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of invoices",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request, InvoiceRepositoryInterface $repository)
    {
        return $this->response->array([
            'data' => $repository

                // optionally, pass jsonapi request queries onto this queryable repo
                // to avoid the need to write boilerplate where, sort, limit in the repo
                ->withRequest($request)

                // get the resulting model collection/array
                ->get([])
        ]);
    }

    /**
     * @param $id
     * @param Request $request
     * @param InvoiceRepositoryInterface $repository
     * @return \Dingo\Api\Http\Response
     *
     * @OA\Get(
     *     path="/invoice/{$id}",
     *     @OA\Parameter(
     *          name="id",
     *          in="path"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a single invoice record",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function show($id, Request $request, InvoiceRepositoryInterface $repository)
    {
        return $this->response->array([
            'data' => $repository
                ->withRequest($request) // pass jsonapi request queries onto this queryable repo
                ->find($id)
        ]);
    }
}
