<?php

namespace App\Http\Controllers\v1\Dms\Printer;

use App\Http\Controllers\RestfulController;
use App\Repositories\Dms\Printer\FormRepositoryInterface;
use App\Http\Requests\Dms\Printer\GetFormRequest;
use App\Http\Requests\Dms\Printer\ShowFormRequest;
use App\Transformers\Dms\Printer\FormTransformer;
use Dingo\Api\Http\Request;

class FormController extends RestfulController 
{
    /**     
     * @var App\Services\Dms\Printer\FormRepositoryInterface
     */
    protected $repository;
    
    /**
     * Create a new controller instance.
     *
     * @param  App\Services\Dms\Printer\FormRepositoryInterface $repository
     */
    public function __construct(FormRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }
    
    public function index(Request $request) 
    {
        $request = new GetFormRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->collection($this->repository->getAll($request->all()), new FormTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
    
    public function show(int $id) 
    {
        $request = new ShowFormRequest(['id' => $id]);
        
        if ($request->validate()) {
            return $this->response->item($this->repository->get(['id' => $id]), new FormTransformer());
        }
        
        return $this->response->errorBadRequest();
    }
}
