<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Inventory\Manufacturer\IndexManufacturerRequest;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\Inventory\InventoryService;
use App\Transformers\Inventory\ManufacturerTransformer;
use Dingo\Api\Http\Response;

class ManufacturerController extends AbstractRestfulController
{
    public function __construct(
        private ManufacturerTransformer $manufacturerTransformer,
        private InventoryService $inventoryService
    ) {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function index(IndexRequestInterface $request): Response
    {
        if ($request->validate()) {
            $manufacturers = $this->inventoryService->getManufacturers();
            return $this->response->collection($manufacturers, $this->manufacturerTransformer);
        }
        return $this->response->errorBadRequest();
    }

    /**
     * {@inheritDoc}
     */
    public function create(CreateRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     */
    public function show(int $id)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, UpdateRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     */
    public function destroy(int $id)
    {
        throw new NotImplementedException();
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(IndexRequestInterface::class, function () {
            return inject_request_data(IndexManufacturerRequest::class);
        });
    }
}
