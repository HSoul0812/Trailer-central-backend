<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Inventory;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Inventory\IndexInventoryRequest;
use App\Http\Requests\Inventory\CreateInventoryRequest;
use App\Http\Requests\Inventory\UpdateInventoryRequest;
use App\Http\Requests\Inventory\DeleteInventoryRequest;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\Inventory\InventoryServiceInterface;
use App\Services\Stripe\StripePaymentServiceInterface;
use App\Transformers\Inventory\InventoryListResponseTransformer;
use App\Transformers\Inventory\TcApiResponseInventoryTransformer;
use App\Transformers\Inventory\TcApiResponseInventoryCreateTransformer;
use App\Transformers\Inventory\TcApiResponseInventoryDeleteTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InventoryController extends AbstractRestfulController
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct(
        private InventoryServiceInterface $inventoryService,
        private TcApiResponseInventoryTransformer $transformer,
        private StripePaymentServiceInterface $paymentService
    )
    {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function create(CreateRequestInterface $request)
    {
        $user = auth('api')->user();
        if ($request->validate()) {
            return $this->response->item(
                $this->inventoryService->create($user->tc_user_id, $request->all()),
                new TcApiResponseInventoryCreateTransformer()
            );
        }

        return $this->response->errorBadRequest();
    }

    /**
     * {@inheritDoc}
     */
    public function destroy(int $id)
    {
        $inventoryRequest = new DeleteInventoryRequest(['inventory_id' => $id]);
        $user = auth('api')->user();

        if ($inventoryRequest->validate()) {
            return $this->response->item(
                $this->inventoryService->delete($user->tc_user_id, $id),
                new TcApiResponseInventoryDeleteTransformer()
            );
        }

        return $this->response->errorBadRequest();
    }

    /**
     * {@inheritDoc}
     */
    public function index(IndexRequestInterface $request): Response
    {
        if($request->validate()) {
            $result = $this->inventoryService->list($request->all());
            return $this->response->item($result, new InventoryListResponseTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * {@inheritDoc}
     */
    public function show(int $id): Response
    {
        $data = $this->inventoryService->show($id);

        return $this->response->item($data, $this->transformer);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, UpdateRequestInterface $request)
    {
        $inventoryRequest = new UpdateInventoryRequest(array_merge($request->all(), ['inventory_id' => $id]));
        $user = auth('api')->user();

        if ($inventoryRequest->validate()) {
            return $this->response->item(
                $this->inventoryService->update($user->tc_user_id, $inventoryRequest->all()),
                new TcApiResponseInventoryCreateTransformer()
            );
        }

        return $this->response->errorBadRequest();
    }

    public function saveProgress(Request $request) {
        $user = auth('api')->user();
        $progress = $request->all();
        \Cache::forever($user->getAuthIdentifier() . '/trailer-progress', json_encode($progress));
        return $this->response->noContent();
    }

    public function getProgress(Request $request): Response
    {
        $user = auth('api')->user();
        return $this->response->array(
            json_decode(\Cache::get($user->getAuthIdentifier() . '/trailer-progress', '{}'), true)
        );
    }

    public function pay(Request $request, $inventoryId, $planId): Redirector|Application|RedirectResponse
    {
        $inventory = $this->inventoryService->show((int)$inventoryId);
        $user = auth('api')->user();
        if($inventory->dealer->id != $user->tc_user_id) {
            throw new HttpException(422, "User should be owner of inventory");
        } else {
            return $this->paymentService->createCheckoutSession($planId, [
                'inventory_id' => $inventoryId,
                'user_id' => $user->tc_user_id
            ]);
        }
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(IndexRequestInterface::class, function () {
            return inject_request_data(IndexInventoryRequest::class);
        });

        app()->bind(CreateRequestInterface::class, function () {
            return inject_request_data(CreateInventoryRequest::class);
        });

        app()->bind(UpdateRequestInterface::class, function () {
            return inject_request_data(UpdateInventoryRequest::class);
        });
    }
}
