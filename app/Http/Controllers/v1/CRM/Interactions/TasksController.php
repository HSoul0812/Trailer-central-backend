<?php

namespace App\Http\Controllers\v1\CRM\Interactions;

use App\Http\Controllers\RestfulController;
use App\Repositories\CRM\Interactions\InteractionsRepositoryInterface;
use App\Http\Requests\CRM\Interactions\GetTasksRequest;
use App\Transformers\CRM\Interactions\TaskTransformer;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\Interactions\GetTasksSortFieldsRequest;
use App\Http\Requests\CRM\Interactions\GetContactDateRequest;
use App\Services\CRM\Interactions\InteractionServiceInterface;

class TasksController extends RestfulController
{
    protected $interactionService;

    protected $interactions;
    
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param Repository $interactions
     */
    public function __construct(interactionServiceInterface $interactionService, 
        InteractionsRepositoryInterface $interactions, TaskTransformer $transformer)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index']);
        $this->middleware('setSalesPersonIdOnRequest')->only(['index']);
        $this->interactionService = $interactionService;
        $this->interactions = $interactions;
        $this->transformer = $transformer;
    }

    public function index(Request $request) 
    {
        $request = new GetTasksRequest($request->all());

        if ($request->validate()) {
            if ($request->sales_person_id) {
                return $this->response->paginator($this->interactions->getTasksBySalespersonId($request->sales_person_id, $request->sort, $request->per_page), $this->transformer);
            } else {
                return $this->response->paginator($this->interactions->getTasksByDealerId($request->dealer_id, $request->sort, $request->per_page), $this->transformer);
            }
        }
        
        return $this->response->errorBadRequest();
    }
    
    public function sortFields(Request $request) {
        $request = new GetTasksSortFieldsRequest($request->all());

        if ($request->validate()) {             
            return $this->response->array([ 'data' => $this->interactions->getTasksSortFields() ]);
        }
        
        return $this->response->errorBadRequest();
    }

    public function getContactDate(int $leadId, Request $request)
    {
        $request = new GetContactDateRequest(array_merge(['lead_id' => $leadId], $request->all()));

        if ($request->validate()) {       
            return $this->response->array(['data' => $this->interactionService->getContactDate($leadId)]);
        }
        
        return $this->response->errorBadRequest();
    }
}
