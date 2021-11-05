<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Ecommerce;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Ecommerce\GetAllRefundsRequest;
use App\Http\Requests\Ecommerce\GetSingleRefundRequest;
use App\Http\Requests\Ecommerce\RefundOrderRequest;
use App\Repositories\Ecommerce\RefundRepositoryInterface;
use App\Services\Ecommerce\Payment\PaymentServiceInterface;
use App\Transformers\Ecommerce\RefundTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class RefundController extends RestfulControllerV2
{
    /** @var PaymentServiceInterface */
    private $service;

    /** @var RefundRepositoryInterface */
    private $repository;

    /** @var RefundTransformer */
    private $transformer;

    public function __construct(PaymentServiceInterface   $service,
                                RefundRepositoryInterface $repository,
                                RefundTransformer         $transformer)
    {
        $this->service = $service;
        $this->repository = $repository;
        $this->transformer = $transformer;

        $this->middleware('setDealerIdOnRequest')->only(['create', 'index', 'show']);
    }

    /**
     * @param int $orderId
     * @param Request $request
     * @return Response|void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
     * @throws \Dingo\Api\Exception\ResourceException when there were some validation error
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function create(int $orderId, Request $request): Response
    {
        $refundRequest = new RefundOrderRequest($request->all() + ['order_id' => $orderId]);

        if ($refundRequest->validate()) {
            $refund = $this->service->refund(
                $refundRequest->orderId(),
                $refundRequest->amount(),
                $refundRequest->parts(),
                $refundRequest->reason()
            );

            return $this->createdResponse($refund->id);
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param Request $request
     * @return Response|void
     * @throws \Dingo\Api\Exception\ResourceException when there were some validation error
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function index(Request $request): Response
    {
        $refundRequest = new GetAllRefundsRequest($request->all());

        if ($refundRequest->validate()) {
            return $this->response->paginator(
                $this->repository->getAll(array_merge($refundRequest->all(), ['paged' => true])),
                $this->transformer
            );
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response|void
     *
     * @throws \Dingo\Api\Exception\ResourceException when there were some validation error
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function show(int $id, Request $request): Response
    {
        $refundRequest = new GetSingleRefundRequest($request->all() + ['refund_id' => $id]);

        if ($refundRequest->validate()) {
            return $this->response->item($this->repository->get($refundRequest->refund_id), $this->transformer);
        }

        $this->response->errorBadRequest();
    }
}
