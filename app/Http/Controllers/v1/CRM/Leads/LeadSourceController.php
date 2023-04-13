<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Leads\Source\GetLeadSourceRequest;
use App\Http\Requests\CRM\Leads\Source\DeleteLeadSourceRequest;
use App\Http\Requests\CRM\Leads\Source\CreateLeadSourceRequest;
use App\Repositories\CRM\Leads\SourceRepositoryInterface;
use App\Transformers\CRM\Leads\SourceTransformer;
use Dingo\Api\Http\Request;

class LeadSourceController extends RestfulControllerV2
{
    protected $leads;

    /**
     * @var App\Transformers\CRM\Leads\SourceTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param Repository $sources
     */
    public function __construct(SourceRepositoryInterface $sources)
    {
        $this->sources = $sources;
        $this->transformer = new SourceTransformer;
        $this->middleware('setUserIdOnRequest')->only(['index', 'create', 'destroy']);
    }

    public function index(Request $request) 
    {
        $request = new GetLeadSourceRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {
            return $this->response->collection($this->sources->getAll($requestData), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }

    public function create(Request $request)
    {
        $request = new CreateLeadSourceRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {
            return $this->response->item($this->sources->createOrUpdate($requestData), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }

    public function destroy(int $id, Request $request)
    {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new DeleteLeadSourceRequest($requestData);

        if ($request->validate()) {

            $this->sources->delete($request->all());
            return $this->successResponse();
        }
        
        return $this->response->errorBadRequest();
    }
}
