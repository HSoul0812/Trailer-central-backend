<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Leads\CreateLeadStatusRequest;
use App\Http\Requests\CRM\Leads\GetLeadsStatusRequest;
use App\Http\Requests\CRM\Leads\UpdateLeadStatusRequest;
use App\Repositories\CRM\Leads\StatusRepositoryInterface;
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
     * @var SimpleTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param StatusRepositoryInterface $status
     */
    public function __construct(StatusRepositoryInterface $status)
    {
        $this->statusRepository = $status;
        $this->transformer = new SimpleTransformer;

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
     * @param Request $request
     * @return Response
     *
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function create(Request $request): Response
    {
        $request = new CreateLeadStatusRequest($request->all());

        if (!$request->validate() || !($status = $this->statusRepository->create($request->all()))) {
            return $this->response->errorBadRequest();
        }

        return $this->createdResponse($status->id);
    }

    /**
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

        if (!$request->validate() || !($status = $this->statusRepository->update($request->all()))) {
            return $this->response->errorBadRequest();
        }

        return $this->updatedResponse($status->id);
    }
}
