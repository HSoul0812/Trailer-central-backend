<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Ecommerce;

use App\Exceptions\Ecommerce\RefundException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Ecommerce\Refund\CancelOrderRequest;
use App\Http\Requests\Ecommerce\Refund\GetAllRefundsRequest;
use App\Http\Requests\Ecommerce\Refund\GetSingleRefundRequest;
use App\Http\Requests\Ecommerce\Refund\IssueRefundOrderRequest;
use App\Http\Requests\Ecommerce\Refund\UpdateRefundTextrailRequest;
use App\Models\Ecommerce\Refund;
use App\Repositories\Ecommerce\RefundRepositoryInterface;
use App\Services\Ecommerce\Refund\RefundBag;
use App\Services\Ecommerce\Refund\RefundServiceInterface;
use App\Transformers\Ecommerce\RefundTransformer;
use Dingo\Api\Exception\ResourceException;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RefundController extends RestfulControllerV2
{
    /** @var RefundServiceInterface */
    private $service;

    /** @var RefundRepositoryInterface */
    private $repository;

    /** @var RefundTransformer */
    private $transformer;

    public function __construct(RefundServiceInterface    $service,
                                RefundRepositoryInterface $repository,
                                RefundTransformer         $transformer)
    {
        $this->service = $service;
        $this->repository = $repository;
        $this->transformer = $transformer;

        $this->middleware('setDealerIdOnRequest')->only(['issue', 'index', 'show']);
    }

    /**
     * It will create a full/partial refund in our database, then it will send a return request to TexTrail,
     * but the refund process on the payment gateway will be remaining as pending until TextTrail send us a command to proceed.
     *
     * @param int $orderId
     * @param Request $request
     * @return Response|void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
     * @throws \Dingo\Api\Exception\ResourceException when there were some validation error
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there were some error different from bad request or validation error
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function issue(int $orderId, Request $request): Response
    {
        $refundRequest = new IssueRefundOrderRequest($request->all() + ['order_id' => $orderId]);

        if ($refundRequest->validate()) {
            try {
                $refund = $this->service->issue(RefundBag::fromIssueRequest($refundRequest));

                return $this->createdResponse($refund->id);
            } catch (RefundException $exception) {
                throw new ResourceException('Validation Failed', $exception->getErrors(), $exception);
            } catch (\Throwable $exception) {
                throw new HttpException($exception->getCode() > 0 ? $exception->getCode() : 500, $exception->getMessage(), $exception);
            }
        }

        $this->response->errorBadRequest();
    }

    /**
     * It will create a full refund in the database, then it should enqueue a refund process on the payment gateway
     * when it reaches the `return_receive` status.
     *
     * @param int $textrailOrderId
     * @return Response|void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
     * @throws \Dingo\Api\Exception\ResourceException when there were some validation error
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there were some error different from bad request or validation error
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function cancelOrder(int $textrailOrderId): Response
    {
        $returnRequest = new CancelOrderRequest(['textrail_order_id' => $textrailOrderId]);

        if ($returnRequest->validate()) {
            try {
                $refund = $this->service->cancelOrder(RefundBag::fromTextrailOrderId($textrailOrderId));

                return $this->acceptedResponse($refund->id);
            } catch (RefundException $exception) {
                throw new ResourceException('Validation Failed', $exception->getErrors(), $exception);
            } catch (\Throwable $exception) {
                throw new HttpException($exception->getCode() > 0 ? $exception->getCode() : 500, $exception->getMessage(), $exception);
            }
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param Request $request
     * @return Response|void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
     * @throws \Dingo\Api\Exception\ResourceException when there were some validation error
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there were some error different from bad request or validation error
     *
     * @noinspection PhpDocMissingThrowsInspection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function updateStatus(int $rma, Request $request): Response
    {
        $returnRequest = new UpdateRefundTextrailRequest(array_merge($request->all(), ['Rma' => $rma]));

        if ($returnRequest->validate()) {
            try {
                /** @var Refund $refund */
                $refund = $returnRequest->refund();

                $this->service->updateStatus($refund, $returnRequest->mappedStatus(), $returnRequest->parts());

                return $this->acceptedResponse($refund->id);
            } catch (RefundException $exception) {
                throw new ResourceException('Validation Failed', $exception->getErrors(), $exception);
            } catch (\Throwable $exception) {
                throw new HttpException($exception->getCode() > 0 ? $exception->getCode() : 500, $exception->getMessage(), $exception);
            }
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
