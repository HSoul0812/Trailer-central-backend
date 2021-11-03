<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Ecommerce;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Ecommerce\RefundOrderRequest;
use App\Services\Ecommerce\Payment\PaymentServiceInterface;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class RefundController extends RestfulControllerV2
{
    /** @var PaymentServiceInterface */
    private $service;

    public function __construct(PaymentServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @param int $orderId
     * @param  Request  $request
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
        $refundRequest = new RefundOrderRequest(array_merge($request->all(), ['order_id' => $orderId]));

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
}
