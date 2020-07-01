<?php


namespace App\Http\Controllers\v1\Dms;


use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\CRM\Payment\PaymentRepositoryInterface;
use Dingo\Api\Http\Request;
use OpenApi\Annotations as OA;

/**
 * Class PaymentController
 *
 * Controller for qb_payment objects
 *
 * @package App\Http\Controllers\v1\Dms
 */
class PaymentController extends RestfulControllerV2
{
    /**
     * Return a single payment object
     *
     * @param $id
     * @param Request $request
     * @param PaymentRepositoryInterface $repository
     * @return \Dingo\Api\Http\Response
     *
     * @OA\Get(
     *     path="/payment/{$id}",
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
     */
    public function show($id, Request $request, PaymentRepositoryInterface $repository)
    {
        return $this->response->array([
            'data' => $repository
                ->withRequest($request) // pass jsonapi request queries onto this queryable repo
                ->find($id)
        ]);
    }
}
