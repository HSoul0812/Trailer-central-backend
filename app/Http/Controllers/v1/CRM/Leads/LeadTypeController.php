<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\CRM\Leads\GetLeadsTypeRequest;
use App\Repositories\CRM\Leads\TypeRepositoryInterface;
use App\Transformers\SimpleTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class LeadTypeController extends RestfulController
{
    protected $types;

    /**
     * @var App\Transformers\SimpleTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param Repository $types
     */
    public function __construct(TypeRepositoryInterface $types)
    {
        $this->types = $types;
        $this->transformer = new SimpleTransformer;
    }

    public function index(Request $request) {
        $request = new GetLeadsTypeRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {
            return $this->response->collection($this->types->getAllUnique(), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @return Response
     */
    public function publicTypes(): Response
    {
        return $this->response->collection($this->types->getAllPublic(), $this->transformer);
    }
}
