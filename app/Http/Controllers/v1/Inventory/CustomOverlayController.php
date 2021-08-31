<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulController;
use App\Models\Inventory\CustomOverlay;
use Dingo\Api\Http\Request; 
use App\Transformers\Inventory\CustomOverlayTransformer;
use App\Http\Requests\Inventory\GetCustomOverlaysRequest;

/**
 * Class CustomOverlayController
 * @package App\Http\Controllers\v1\Inventory
 */
class CustomOverlayController extends RestfulController
{
    
    public function __construct()
    {
        $this->middleware('setDealerIdOnRequest')->only(['index']);
    }
    
    public function index(Request $request) 
    {        
        $request = new GetCustomOverlaysRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->collection(CustomOverlay::where('dealer_id', $request->dealer_id)->get(), new CustomOverlayTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
}
