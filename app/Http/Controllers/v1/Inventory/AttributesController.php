<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Inventory\Attributes\IndexAttributesRequest;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\Inventory\InventoryServiceInterface;
use App\Transformers\Inventory\TcApiResponseAttributeTransformer;
use Dingo\Api\Http\Response;

class AttributesController extends AbstractRestfulController
{
    public function __construct(
        private InventoryServiceInterface $inventoryService,
        private TcApiResponseAttributeTransformer $transformer)
    {
        parent::__construct();
    }

    public function index(IndexRequestInterface $request): Response
    {
        if ($request->validate()) {
            $result = $this->inventoryService->attributes($request->all());

            return $this->response->collection($result, $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    public function create(CreateRequestInterface $request)
    {
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
            return inject_request_data(IndexAttributesRequest::class);
        });
    }
}
