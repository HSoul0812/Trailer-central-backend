<?php

namespace App\Http\Controllers\v1\Parts;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Exceptions\NotImplementedException;
use App\Repositories\Repository;
use App\Http\Requests\Parts\CreatePartRequest;
use App\Http\Requests\Parts\DeletePartRequest;
use App\Transformers\Parts\PartsTransformer;
use App\Http\Requests\Parts\ShowPartRequest;
use App\Http\Requests\Parts\GetPartsRequest;
use App\Http\Requests\Parts\UpdatePartRequest;

class PartsController extends RestfulController
{
    
    protected $parts;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Repository $parts)
    {
        $this->parts = $parts;
    }

    public function create(Request $request) {
        $request = new CreatePartRequest($request->all());
        
        if ( $request->validate() ) {
            return $this->response->item($this->parts->create($request->all()), new PartsTransformer());
        }  
        
        return $this->response->errorBadRequest();
    }

    public function destroy(int $id) {
        $request = new DeletePartRequest(['id' => $id]);
        
        if ( $request->validate() && $this->parts->delete(['id' => $id])) {
            return $this->response->noContent();
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/parts",
     *     @OA\Response(
     *         response="200",
     *         description="Returns part data",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request) {
        $request = new GetPartsRequest($request->all());
        
        if ( $request->validate() ) {
            return $this->response->paginator($this->parts->getAll($request->all()), new PartsTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    public function show(int $id) {
        $request = new ShowPartRequest(['id' => $id]);
        
        if ( $request->validate() ) {
            return $this->response->item($this->parts->get(['id' => $id]), new PartsTransformer());
        }
        
        return $this->response->errorBadRequest();
    }

    public function update(int $id, Request $request) {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdatePartRequest($requestData);
        
        if ( $request->validate() ) {
            return $this->response->item($this->parts->update($request->all()), new PartsTransformer());
        }
        
        throw new NotImplementedException();
    }

}
