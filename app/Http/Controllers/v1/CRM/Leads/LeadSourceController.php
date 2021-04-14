<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\CRM\Leads\GetLeadsSourceRequest;
use App\Repositories\CRM\Leads\SourceRepositoryInterface;
use App\Transformers\CRM\Leads\SourceTransformer;
use Dingo\Api\Http\Request;

class LeadSourceController extends RestfulController
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
    }

    public function index(Request $request) {
        $request = new GetLeadsSourceRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {
            return $this->response->collection($this->sources->getAll($request->all()), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }
}
