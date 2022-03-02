<?php

namespace App\Http\Controllers\v1\Marketing\Craigslist;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Marketing\Craigslist\GetInventoryRequest;
use App\Repositories\Marketing\Craigslist\InventoryRepositoryInterface;
use App\Transformers\Marketing\Craigslist\InventoryTransformer;
use Dingo\Api\Http\Request;

class InventoryController extends RestfulControllerV2
{
    /**
     * @var InventoryRepositoryInterface
     */
    protected $repository;

    /**
     * @var InventoryTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param InventoryRepositoryInterface $repo
     * @param InventoryTransformer $transformer
     */
    public function __construct(
        InventoryRepositoryInterface $repo,
        InventoryTransformer $transformer
    ) {
        $this->repository = $repo;
        $this->transformer = $transformer;

        $this->middleware('setDealerIdOnRequest')->only(['index']);
    }

    /**
     * Get Craigslist Inventory
     * 
     * @param Request $request
     * @return type
     */
    public function index(Request $request)
    {
        // Handle Get Inventory Request
        $request = new GetInventoryRequest($request->all());
        if ($request->validate()) {
            // Get Inventory
            return $this->response->paginator($this->repository->getAll($request->all(), true), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }
}