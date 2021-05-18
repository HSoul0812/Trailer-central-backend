<?php

namespace App\Http\Controllers\v1\Dms\Printer;

use App\Http\Controllers\RestfulController;
use App\Services\Dms\Printer\OkidataRepositoryInterface;
use App\Http\Requests\Dms\Printer\GetOkidataRequest;
use App\Http\Requests\Dms\Printer\ShowOkidataRequest;
use Dingo\Api\Http\Request;

class OkidataController extends RestfulController 
{
    /**     
     * @var App\Services\Dms\Printer\OkidataRepositoryInterface
     */
    protected $repository;
    
    /**
     * Create a new controller instance.
     *
     * @param  App\Services\Dms\Printer\OkidataRepositoryInterface $okidataRepo
     */
    public function __construct(OkidataRepositoryInterface $okidataRepo)
    {
        $this->repository = $okidataRepo;
    }
    
    public function index(Request $request) 
    {
        $request = new GetOkidataRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->collection($this->repository->getAll($request->all()), new OkidataTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
    
    public function show(int $id) 
    {
        $request = new ShowOkidataRequest(['id' => $id]);
        
        if ($request->validate()) {
            return $this->response->item($this->repository->get(['id' => $id]), new OkidataTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
}
