<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Inventory;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Inventory\IndexInventoryRequest;
use App\Http\Requests\Inventory\CreateInventoryRequest;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\Inventory\InventoryServiceInterface;
use App\Transformers\Inventory\InventoryListResponseTransformer;
use App\Transformers\Inventory\TcApiResponseInventoryTransformer;
use App\Transformers\Inventory\TcApiResponseInventoryCreateTransformer;
use Dingo\Api\Http\Response;

class InventoryController extends AbstractRestfulController
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct(
        private InventoryServiceInterface $inventoryService,
        private InventoryServiceInterface $inventoryRepository,
        private TcApiResponseInventoryTransformer $transformer)
    {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function create(CreateRequestInterface $request)
    {
      if ($request->validate()) {
          return $this->response->item($this->inventoryService->create($request->all()), new TcApiResponseInventoryCreateTransformer());
      }

      return $thqis->response->errorBadRequest();
    }

    /**
     * {@inheritDoc}
     */
    public function destroy(int $id)
    {
        throw new NotImplementedException();
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

        $this->response->errorBadRequest();
    }

    /**
     * {@inheritDoc}
     */
    public function show(int $id): Response
    {
        $data = $this->inventoryRepository->show($id);

        return $this->response->item($data, $this->transformer);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, UpdateRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(IndexRequestInterface::class, function () {
            return inject_request_data(IndexInventoryRequest::class);
        });
        
        app()->bind(CreateRequestInterface::class, function () {
            return inject_request_data(CreateInventoryRequest::class);
        });
    }
}
