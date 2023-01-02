<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Inventory\Cache\InvalidateByDealerRequest;
use Dingo\Api\Http\Request;

class InventoryCacheController extends RestfulControllerV2
{
    public function __construct()
    {
        $this->middleware('inventory.cache.permission');
    }

    /**
     * @param Request $request
     * @return void
     * @throws \App\Exceptions\Requests\Validation\NoObjectIdValueSetException
     * @throws \App\Exceptions\Requests\Validation\NoObjectTypeSetException
     */
    public function invalidateByDealer(Request $request)
    {
        $request = new InvalidateByDealerRequest($request->all());

        if ($request->validate()) {
            // Invalidate Cache By Request
        }

        return $this->response->errorBadRequest();
    }
}
