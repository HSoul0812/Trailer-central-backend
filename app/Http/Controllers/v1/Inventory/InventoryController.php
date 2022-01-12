<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\Inventory\InventoryServiceInterface;

class InventoryController extends AbstractRestfulController
{
    public function __construct(private InventoryServiceInterface $inventoryService)
    {
        parent::__construct();
    }

    public function index(IndexRequestInterface $request)
    {
        $this->inventoryService->list($request->all());
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

    }
}
