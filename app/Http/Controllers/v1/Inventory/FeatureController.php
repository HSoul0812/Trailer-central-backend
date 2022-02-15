<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulController;
use App\Transformers\Inventory\AttributeTransformer;
use Dingo\Api\Http\Request;
use App\Models\Inventory\InventoryFeatureList;
use App\Transformers\Inventory\FeatureListTransformer;

/**
 * Class FeatureController
 * @package App\Http\Controllers\v1\Inventory
 */
class FeatureController extends RestfulController
{
    public function index(Request $request)
    {
        return $this->response->collection(InventoryFeatureList::all(), new FeatureListTransformer());
    }
}
