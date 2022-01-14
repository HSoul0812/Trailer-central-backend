<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Inventory\InventoryRequest;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\Inventory\InventoryServiceInterface;
use App\Transformers\Inventory\InventoryListResponseTransformer;
use App\Transformers\Inventory\InventoryTransformer;
use Dingo\Api\Http\Response;

class InventoryController extends AbstractRestfulController
{
    public function __construct(private InventoryServiceInterface $inventoryService)
    {
        parent::__construct();
    }

    public function index(IndexRequestInterface $request): Response
    {
        $result = $this->inventoryService->list($request->all());
        return $this->response->item($result, new InventoryListResponseTransformer());
    }

    public function create(CreateRequestInterface $request)
    {
        // TODO: Implement create() method.
        throw new NotImplementedException();
    }

    public function show(int $id)
    {
        throw new NotImplementedException();
    }

    public function update(int $id, UpdateRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    public function destroy(int $id)
    {
        throw new NotImplementedException();
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(IndexRequestInterface::class, function () {
            return inject_request_data(InventoryRequest::class);
        });
    }
}
