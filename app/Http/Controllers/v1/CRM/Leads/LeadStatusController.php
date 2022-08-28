<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Leads\CreateLeadStatusRequest;
use App\Http\Requests\CRM\Leads\GetLeadsStatusRequest;
use App\Http\Requests\CRM\Leads\UpdateLeadStatusRequest;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
use App\Services\CRM\Leads\LeadStatusServiceInterface;
use App\Transformers\SimpleTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class LeadStatusController extends RestfulControllerV2
{
    /**
     * @var StatusRepositoryInterface
     */
    protected $statusRepository;

    /**
     * @var LeadStatusServiceInterface
     */
    protected $service;

    /**
     * @var SimpleTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param StatusRepositoryInterface $status
     * @param LeadStatusServiceInterface $service
     */
    public function __construct(StatusRepositoryInterface $status, LeadStatusServiceInterface $service)
    {
        $this->statusRepository = $status;
        $this->transformer = new SimpleTransformer;
        $this->service = $service;

        $this->middleware('setDealerIdOnRequest')->only(['create', 'update']);
    }

    public function index(Request $request)
    {
        $request = new GetLeadsStatusRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {
            return $this->response->collection($this->statusRepository->getAll($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Put(
     *     path="/api/leads/status",
     *     description="Create a lead status",
     *     tags={"Lead"},
     *     @OA\Parameter(
     *         name="tc_lead_identifier",
     *         in="query",
     *         description="Lead Id",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Lead Status",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sales_person_id",
     *         in="query",
     *         description="Sales Person Id",
     *         required=false,
     *         @OA\Schema(type="integer")
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
     * @param Request $request
     * @return Response
     *
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function create(Request $request): Response
    {
        $request = new CreateLeadStatusRequest($request->all());

        if (!$request->validate() || !($status = $this->service->create($request->all()))) {
            return $this->response->errorBadRequest();
        }

        return $this->createdResponse($status->id);
    }

    /**
     * @OA\Post(
     *     path="/api/leads/status/{id}",
     *     description="Update a lead status",
     *     tags={"Lead"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Lead Status Id",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="tc_lead_identifier",
     *         in="query",
     *         description="Lead Id",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Lead Status",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sales_person_id",
     *         in="query",
     *         description="Sales Person Id",
     *         required=false,
     *         @OA\Schema(type="integer")
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
     *
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function update(int $id, Request $request): Response
    {
        $request = new UpdateLeadStatusRequest(array_merge($request->all(), ['id' => $id]));

        if (!$request->validate() || !($status = $this->service->update($request->all()))) {
            return $this->response->errorBadRequest();
        }

        return $this->updatedResponse($status->id);
    }

    /**
     * @return Response
     */
    public function publicStatuses(): Response
    {
        return $this->response->collection($this->statusRepository->getAllPublic(), $this->transformer);
    }
}
