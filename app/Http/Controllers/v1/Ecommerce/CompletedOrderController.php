<?php

namespace App\Http\Controllers\v1\Ecommerce;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Ecommerce\CreateCompletedOrderRequest;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use App\Repositories\Ecommerce\CompletedOrderRepositoryInterface;
use App\Services\Ecommerce\CompletedOrder\CompletedOrderServiceInterface;
use App\Transformers\Ecommerce\CompletedOrderTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class CompletedOrderController extends RestfulController
{
    /** @var CompletedOrderServiceInterface */
    private $completedOrderService;

    /** @var CompletedOrderRepositoryInterface */
    private $completedOrderRepo;

    /**
     * CompletedOrderController constructor.
     * @param CompletedOrderServiceInterface $completedOrderService
     * @param CompletedOrderRepositoryInterface $completedOrderRepo
     */
    public function __construct(CompletedOrderServiceInterface $completedOrderService, CompletedOrderRepositoryInterface $completedOrderRepo)
    {
        $this->completedOrderService = $completedOrderService;
        $this->completedOrderRepo = $completedOrderRepo;
    }


    public function index(Request $request): Response
    {
        return $this->response->paginator($this->completedOrderRepo->getAll($request->all()), new CompletedOrderTransformer());
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \App\Exceptions\Requests\Validation\NoObjectIdValueSetException
     * @throws \App\Exceptions\Requests\Validation\NoObjectTypeSetException
     */
    public function create(Request $request): Response
    {
        $orderCreateRequest = new CreateCompletedOrderRequest($request->all());

        if (!$orderCreateRequest->validate()) {
            return $this->response->errorBadRequest();
        }

        $order = $this->completedOrderService->create($orderCreateRequest->all());

        return $this->createdResponse($order->id);
    }

    public function show(int $id)
    {
        return $this->response->item($this->completedOrderRepo->get(['id' => $id]), new CompletedOrderTransformer());
    }
}