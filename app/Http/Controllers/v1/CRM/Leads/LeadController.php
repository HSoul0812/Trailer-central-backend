<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Leads\AssignLeadRequest;
use App\Http\Requests\CRM\Leads\FirstLeadRequest;
use App\Http\Requests\CRM\Leads\GetLeadsSortFieldsCrmRequest;
use App\Http\Requests\CRM\Leads\MergeLeadsRequest;
use App\Http\Requests\CRM\Leads\GetLeadsSortFieldsRequest;
use App\Http\Requests\CRM\Leads\GetUniqueFullNamesRequest;
use App\Http\Requests\CRM\Leads\UpdateLeadRequest;
use App\Http\Requests\CRM\Leads\CreateLeadRequest;
use App\Http\Requests\CRM\Leads\GetLeadRequest;
use App\Http\Requests\CRM\Leads\GetLeadsRequest;
use App\Http\Requests\CRM\Leads\GetLeadsMatchesRequest;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Transformers\CRM\Leads\GetUniqueFullNamesTransformer;
use App\Transformers\CRM\Leads\LeadTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use App\Http\Requests\CRM\Leads\DeleteLeadRequest;

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
        $this->middleware('setDealerIdOnRequest')->only(['index', 'update', 'create', 'show', 'first', 'assign', 'getMatches', 'mergeLeads', 'uniqueFullNames', 'destroy']);
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
                        ->addMeta('lead_counts', $this->leads->getLeadStatusCountByDealer($requestData['dealer_id'], $requestData))
                        ->addMeta('edge_date', $this->leads->getEdgeDate($requestData));
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

    /**
     * @throws NoObjectTypeSetException
     * @throws NoObjectIdValueSetException
     */
    public function first(Request $request): Response
    {
        $request = new FirstLeadRequest($request->all());

        if ($request->validate()) {
            return $this->response->item($this->leads->first($request->all()), $this->transformer);
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

    public function sortFieldsCrm(Request $request) : Response
    {
        $request = new GetLeadsSortFieldsCrmRequest($request->all());

        if ($request->validate()) {
            return $this->response->array([ 'data' => $this->leads->getLeadsSortFieldsCrm() ]);
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
     * @OA\Get(
     *     path="/api/leads/unique-full-names",
     *     description="Retrieve a list of unique leads fullnames",
     *     tags={"Lead"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Page Limit",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="is_archived",
     *         in="query",
     *         description="Archived or not",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         in="query",
     *         description="Search term",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of unique leads fullnames",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param Request $request
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function uniqueFullNames(Request $request): Response
    {
        $request = new GetUniqueFullNamesRequest($request->all());

        if ($request->validate()) {
            return $this->response->paginator($this->leads->getUniqueFullNames($request->all()), new GetUniqueFullNamesTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     *  @OA\Post(
     *     path="/api/leads/{id}/merge",
     *     description="Merge Leads",
     *     tags={"Lead"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Lead Id",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="merges_lead_id",
     *         in="query",
     *         description="Array With Lead Ids",
     *         required=true,
     *         @OA\Schema(type="array", @OA\Items(type="integer"))
     *     ),
     *
     *     @OA\Response(
     *         response="200",
     *         description="Returns a part id",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
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

        $this->service->mergeLeads($id, $request->get('merge_lead_ids'));

        return $this->updatedResponse();
    }

    public function output(Request $request)
    {
        $request = new GetLeadsRequest($request->all());
        
        if ($request->validate()) {
            return $this->leads->output($request->all());
        }
    }

    public function destroy(int $id, Request $request)
    {
        $request = new DeleteLeadRequest(array_merge($request->all(), ['id' => $id]));
        
        if ($request->validate() 
            && $this->leads->delete(['id' => $id, 'dealer_id' => $request->get('dealer_id')]) > 0) {

            return $this->updatedResponse();
        }

        return $this->response->errorBadRequest();
    }
}
