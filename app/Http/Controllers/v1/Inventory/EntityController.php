<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Http\Controllers\RestfulController;
use App\Models\Inventory\EntityType;
use Dingo\Api\Http\Request; 
use App\Transformers\Inventory\EntityTransformer;

/**
 * Class EntityController
 * @package App\Http\Controllers\v1\Inventory
 */
class EntityController extends RestfulController
{
    public function index(Request $request) {        
        return $this->response->collection(EntityType::all(), new EntityTransformer);
    }
}
