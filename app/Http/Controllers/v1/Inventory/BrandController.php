<?php

namespace App\Http\Controllers\v1\Inventory;

use App\DTOs\Inventory\TcApiResponseBrand;
use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Inventory\Brand\IndexBrandRequest;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\Inventory\InventoryService;
use App\Transformers\Inventory\BrandTransformer;
use Dingo\Api\Http\Response;
use Illuminate\Support\Facades\Http;

class BrandController extends AbstractRestfulController
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct(
        private BrandTransformer $brandTransformer,
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
            $brands = $this->inventoryService->getBrands();

            return $this->response->collection($brands, $this->brandTransformer);
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
    public function destroy(int $id)
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

    protected function constructRequestBindings(): void
    {
        app()->bind(IndexRequestInterface::class, function () {
            return inject_request_data(IndexBrandRequest::class);
        });
    }
}
