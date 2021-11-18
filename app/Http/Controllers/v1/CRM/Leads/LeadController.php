<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Leads\AssignLeadRequest;
use App\Http\Requests\CRM\Leads\GetLeadsRequest;
use App\Http\Requests\CRM\Leads\GetLeadsSortFieldsRequest;
use App\Http\Requests\CRM\Leads\MergeLeadsRequest;
use App\Http\Requests\CRM\Leads\UpdateLeadRequest;
use App\Http\Requests\CRM\Leads\CreateLeadRequest;
use App\Http\Requests\CRM\Leads\GetLeadRequest;
use App\Http\Requests\CRM\Leads\GetLeadsMatchesRequest;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Transformers\CRM\Leads\LeadTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class LeadController extends RestfulControllerV2
{
    /**
     * @var App\Repositories\CRM\Leads\LeadRepositoryInterface
     */
    protected $leads;

    /**
     * @var App\Services\CRM\Leads\LeadServiceInterface
     */
    protected $service;

    /**
     * @var App\Transformers\CRM\Leads\LeadTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param Repository $leads
     */
    public function __construct(LeadRepositoryInterface $leads, LeadServiceInterface $service)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'update', 'create', 'show', 'assign', 'getMatches', 'mergeLeads']);
        $this->middleware('setWebsiteIdOnRequest')->only(['index', 'update', 'create']);
        $this->leads = $leads;
        $this->service = $service;
        $this->transformer = new LeadTransformer;
    }

    public function index(Request $request) {
        $request = new GetLeadsRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {
            return $this->response->paginator($this->leads->getAll($requestData), $this->transformer)
                        ->addMeta('lead_counts', $this->leads->getLeadStatusCountByDealer($requestData['dealer_id'], $requestData));
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Display data about the record in the DB
     *
     * @param int $id
     */
    public function show(int $id)
    {
        $request = new GetLeadRequest(['id' => $id]);

        if ($request->validate()) {
            return $this->response->item($this->leads->get(['id' => $id]), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    public function create(Request $request) {
        $request = new CreateLeadRequest($request->all());

        if ($request->validate()) {
            return $this->response->item($this->service->create($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    public function update(int $id, Request $request) {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateLeadRequest($requestData);

        if ($request->validate()) {
            return $this->response->item($this->service->update($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    public function sortFields(Request $request) {
        $request = new GetLeadsSortFieldsRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {
            return $this->response->array([ 'data' => $this->leads->getLeadsSortFields() ]);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response|void
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function assign(int $id, Request $request): Response
    {
        $request = new AssignLeadRequest(array_merge($request->all(), ['id' => $id]));

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $lead = $this->service->assign($request->all());

        return $this->updatedResponse($lead->identifier);
    }

    /**
     * @param Request $request
     * @return Response|void
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function getMatches(Request $request)
    {
        $request = new GetLeadsMatchesRequest($request->all());

        if ($request->validate()) {
            return $this->response->collection(
                $this->service->getMatches($request->all()),
                $this->transformer
            );
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function mergeLeads(int $id, Request $request): Response
    {
        $request = new MergeLeadsRequest(array_merge($request->all(), ['lead_id' => $id]));

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $this->service->mergeLeads($id, $request->get('merges_lead_id'));

        return $this->updatedResponse();
    }
}
