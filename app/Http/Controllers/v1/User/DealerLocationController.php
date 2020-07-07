<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulController;
use App\Repositories\User\DealerLocationRepositoryInterface;
use App\Transformers\User\DealerLocationTransformer;
use App\Http\Requests\User\GetDealerLocationRequest;
use Dingo\Api\Http\Request;

class DealerLocationController extends RestfulController {
    
    protected $dealerLocation;
    
    protected $transformer;
    
    public function __construct(DealerLocationRepositoryInterface $dealerLocationRepo)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index']);
        $this->dealerLocation = $dealerLocationRepo;
        $this->transformer = new DealerLocationTransformer();
    }     

    public function index(Request $request) {
        $request = new GetDealerLocationRequest($request->all());
        if ($request->validate()) {
            return $this->response->paginator($this->dealerLocation->getAll($request->all()), new DealerLocationTransformer);
        }
        return $this->response->errorBadRequest();
    }
}
