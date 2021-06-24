<?php

namespace App\Http\Controllers\v1\Dms;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Refund\GetRefundsRequest;
use App\Repositories\CRM\Refund\RefundRepositoryInterface;
use App\Transformers\Dms\RefundTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use League\Fractal\Manager;

/**
 * Class RefundController
 * @package App\Http\Controllers\v1\Dms\Payment
 */
class RefundController extends RestfulControllerV2
{
    /**
     * @var RefundRepositoryInterface
     */
    private $refundRepository;

    /**
     * RefundController constructor.
     * @param RefundRepositoryInterface $refundRepository
     * @param Manager $fractal
     */
    public function __construct(RefundRepositoryInterface $refundRepository, Manager $fractal)
    {
        $this->middleware('setDealerIdFilterOnRequest')->only(['index']);

        $this->refundRepository = $refundRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/payments/refunds",
     *     description="Retrieve a list of refunds",
     *     tags={"Payment"},
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort order can be: price,-price,relevance,title,-title,length,-length",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of refunds",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param Request $request
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function index(Request $request): Response
    {
        $request = new GetRefundsRequest($request->all());

        if ($request->validate()) {
            $refunds = $this->refundRepository->withRequest($request)->getAll($request->all());

            return $this->collectionResponse($refunds, new RefundTransformer(), $this->refundRepository->getPaginator());
        }

        return $this->response->errorBadRequest();
    }
}