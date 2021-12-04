<?php

namespace App\Http\Controllers\v1\Ecommerce;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Ecommerce\ApproveCompletedOrderRequest;
use App\Http\Requests\Ecommerce\CreateCompletedOrderRequest;
use App\Http\Requests\Ecommerce\CreateProviderOrderRequest;
use App\Http\Requests\Ecommerce\GetAllCompletedOrderRequest;
use App\Http\Requests\Ecommerce\GetSingleCompletedOrderRequest;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Repositories\Parts\Textrail\PartRepository;
use App\Services\Ecommerce\CompletedOrder\CompletedOrderServiceInterface;
use App\Transformers\Ecommerce\CompletedOrderTransformer;
use App\Transformers\Ecommerce\CompleteOrderRequestTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Http\JsonResponse;

class CompletedOrderController extends RestfulControllerV2
{
    /** @var CompletedOrderServiceInterface */
    private $completedOrderService;

    /** @var CompletedOrderRepositoryInterface */
    private $completedOrderRepo;

    /** @var PartRepository */
    private $textRailPartRepo;

    /**
     * CompletedOrderController constructor.
     * @param CompletedOrderServiceInterface $completedOrderService
     * @param CompletedOrderRepositoryInterface $completedOrderRepo
     * @param PartRepository $textRailPartRepo
     */
    public function __construct(
        CompletedOrderServiceInterface $completedOrderService,
        CompletedOrderRepositoryInterface $completedOrderRepo,
        PartRepositoryInterface $textRailPartRepo
    )
    {
        $this->completedOrderService = $completedOrderService;
        $this->completedOrderRepo = $completedOrderRepo;
        $this->textRailPartRepo = $textRailPartRepo;

        $this->middleware('setDealerIdOnRequest')->only(['index', 'show']);

        /**
         * Since create action will be called from a payment gateway (Stripe), and the dealer website,
         * we need to make sure that the dealer is set on the request only when the request is coming from the dealer website.
         * Then, it will be handle by the repository ensuring that when the order is a new one and the dealer is not set,
         * it will throw an exception.
         */
        $this->middleware('setDealerIdWhenAuthenticatedOnRequest')->only(['create']);
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws \Dingo\Api\Exception\ResourceException when there were some validation error
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function index(Request $request): Response
    {
        $listRequest = new GetAllCompletedOrderRequest($request->all());

        if ($listRequest->validate()) {
            return $this->response
                ->paginator(
                    $this->completedOrderRepo->getAll($listRequest->all()),
                    new CompletedOrderTransformer($this->textRailPartRepo)
                )
                ->addMeta('totals', $this->completedOrderRepo->getGrandTotals($listRequest->dealer_id));
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws \Dingo\Api\Exception\ResourceException when there were some validation error
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function create(Request $request): Response
    {
        $orderCreateRequest = new CreateCompletedOrderRequest($request->all());

        if (!$orderCreateRequest->validate()) {
            return $this->response->errorBadRequest();
        }

        $params = (new CompleteOrderRequestTransformer())->transform($orderCreateRequest);

        $order = $this->completedOrderService->create($params);

        return $this->response->item($order, new CompletedOrderTransformer($this->textRailPartRepo));
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     *
     * @throws \Dingo\Api\Exception\ResourceException when there were some validation error
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function show(int $id, Request $request): Response
    {
        $orderRequest = new GetSingleCompletedOrderRequest($request->all() + ['order_id' => $id]);

        if ($orderRequest->validate()) {
            return $this->response->item($this->completedOrderRepo->get(['id' => $id]), new CompletedOrderTransformer($this->textRailPartRepo));
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param int $textrail_order_id
     * @param Request $request
     * @return Response
     * @throws \Dingo\Api\Exception\ResourceException when there were some validation error
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function markAsApproved(int $textrail_order_id, Request $request): Response
    {
        $approveRequest = new ApproveCompletedOrderRequest($request->all() + ['textrail_order_id' => $textrail_order_id]);

        if ($approveRequest->validate()) {
            return $this->response->item($this->completedOrderService->approve($textrail_order_id), new CompletedOrderTransformer($this->textRailPartRepo));
        }

        return $this->response->errorBadRequest();
    }
}
