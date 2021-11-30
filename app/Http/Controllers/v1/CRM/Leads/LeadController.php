<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Leads\GetLeadsRequest;
use App\Http\Requests\CRM\Leads\GetLeadsSortFieldsRequest;
use App\Http\Requests\CRM\Leads\GetUniqueFullNamesRequest;
use App\Http\Requests\CRM\Leads\UpdateLeadRequest;
use App\Http\Requests\CRM\Leads\CreateLeadRequest;
use App\Http\Requests\CRM\Leads\GetLeadRequest;
use App\Repositories\CRM\Leads\LeadRepositoryInterface;
use App\Services\CRM\Leads\LeadServiceInterface;
use App\Transformers\CRM\Leads\GetUniqueFullNamesTransformer;
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
        $this->middleware('setDealerIdOnRequest')->only(['index', 'update', 'create', 'show', 'uniqueFullNames']);
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
}
