<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Inventory;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Inventory\IndexInventoryRequest;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\Inventory\InventoryServiceInterface;
use Dingo\Api\Http\Response;

class InventoryController extends AbstractRestfulController
{
    /**
     * Create a new controller instance.
     *
     * @param TypeRepositoryInterface   $type
     * @param TypesTransformerInterface $typesTransformer
     */
    public function __construct(InventoryServiceInterface $inventoryService)
    {
        parent::__construct();
        $this->inventoryRepository = $inventoryService;
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
    public function index(IndexRequestInterface $request): Response
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     */
    public function show(int $id)
    {
        return $this->inventoryRepository->show($id);
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
    }
}
