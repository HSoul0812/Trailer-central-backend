<?php

namespace App\Http\Controllers\v1\Parts\Textrail;

use App\Http\Controllers\v1\Parts\PartsController as BasePartsController;
use App\Repositories\Parts\PartRepositoryInterface;
use App\Services\Parts\PartServiceInterface;
use League\Fractal\Manager;
use Dingo\Api\Http\Request;
use App\Exceptions\NotImplementedException;

class PartsController extends BasePartsController 
{
        
   /**
    * 
    * @param Request $request
    * @throws NotImplementedException
    */
    public function create(Request $request) {
        throw new NotImplementedException();
    }

    /**
     * 
     * @param int $id
     * @return type
     */
    public function destroy(int $id) {
        throw new NotImplementedException();
    }

    /**
     * 
     * @param int $id
     * @param Request $request
     * @throws NotImplementedException
     */
    public function update(int $id, Request $request) {
        throw new NotImplementedException();
    }
}
