<?php

namespace App\Http\Controllers\v1\Dms\Printer;

use App\Http\Controllers\RestfulController;
use App\Repositories\Dms\Printer\FormRepositoryInterface;
use App\Http\Requests\Dms\Printer\GetFormRequest;
use App\Http\Requests\Dms\Printer\ShowFormRequest;
use App\Http\Requests\Dms\Printer\InstructionFormRequest;
use App\Services\Dms\Printer\FormServiceInterface;
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
     * @param  App\Repositories\Dms\Printer\FormRepositoryInterface $repository
     * @param  App\Services\Dms\Printer\FormServiceInterface $service
     */
    public function __construct(FormRepositoryInterface $repository, FormServiceInterface $service)
    {
        $this->middleware('setDealerIdOnRequest')->only(['instruction']);
        $this->repository = $repository;
        $this->service = $service;
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
    
    public function instruction(int $id, Request $request)
    {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new InstructionFormRequest($requestData);
        
        if ($request->validate()) {
            return $this->response->array($this->service->getFormInstruction($id, $request->unit_sale_id));
        }
        
        return $this->response->errorBadRequest();
    }
}
