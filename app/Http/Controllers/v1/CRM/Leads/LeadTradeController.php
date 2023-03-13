<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Leads\Trade\GetLeadTradesRequest;
use App\Http\Requests\CRM\Leads\Trade\GetLeadTradeRequest;
use App\Http\Requests\CRM\Leads\Trade\CreateLeadTradeRequest;
use App\Http\Requests\CRM\Leads\Trade\UpdateLeadTradeRequest;
use App\Http\Requests\CRM\Leads\Trade\DeleteLeadTradeRequest;
use App\Repositories\CRM\Leads\LeadTradeRepositoryInterface;
use App\Transformers\CRM\Leads\LeadTradeTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use App\Services\CRM\Leads\LeadTradeServiceInterface;

/**
 * Class LeadTradeController
 * @package App\Http\Controllers\v1\CRM\Leads
 */
class LeadTradeController extends RestfulControllerV2
{
    /**
     * @var LeadTradeRepositoryInterface
     */
    private $leadTradeRepository;

    /**
     * @var LeadTradeServiceInterface
     */
    private $leadTradeService;

    /**
     * @param LeadTradeRepositoryInterface $leadTradeRepository
     */
    public function __construct(LeadTradeRepositoryInterface $leadTradeRepository, LeadTradeServiceInterface $leadTradeService)
    {
        $this->leadTradeRepository = $leadTradeRepository;
        $this->leadTradeService = $leadTradeService;
        $this->transformer = new LeadTradeTransformer();
    }

    /**
     * @param int $leadId
     * @param Request $request
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function index(int $leadId, Request $request): Response
    {
        $requestData = $request->all();
        $requestData['lead_id'] = $leadId;
        $request = new GetLeadTradesRequest($requestData);

        if ($request->validate()) {
            return $this->collectionResponse($this->leadTradeRepository->getAll($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Display data about the record in the DB
     *
     * @param int $id
     */
    public function show(int $leadId, int $id, Request $request)
    {
        $requestData = $request->all();
        $requestData['lead_id'] = $leadId;
        $requestData['id'] = $id;
        $request = new GetLeadTradeRequest($requestData);

        if ($request->validate()) {
            return $this->response->item($this->leadTradeRepository->get(['id' => $id]), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Insert new data into the DB
     *
     * @param int $leadId
     * @param Request $request
     */
    public function create(int $leadId, Request $request) 
    {
        $requestData = $request->all();
        $requestData['lead_id'] = $leadId;
        $request = new CreateLeadTradeRequest($requestData);

        if ($request->validate()) {
            return $this->response->item($this->leadTradeService->create($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Update existing data on the record already in the DB
     *
     * @param int $leadId
     * @param int $id
     * @param Request $request
     */
    public function update(int $leadId, int $id, Request $request) 
    {
        $requestData = $request->all();
        $requestData['lead_id'] = $leadId;
        $requestData['id'] = $id;
        $request = new UpdateLeadTradeRequest($requestData);

        if ($request->validate()) {
            return $this->response->item($this->leadTradeService->update($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Delete existing data on the record already in the DB
     *
     * @param int $leadId
     * @param int $id
     * @param Request $request
     */
    public function destroy(int $leadId, int $id, Request $request)
    {
        $requestData = $request->all();
        $requestData['lead_id'] = $leadId;
        $requestData['id'] = $id;
        $request = new DeleteLeadTradeRequest($requestData);
        
        if ($request->validate() && $this->leadTradeService->delete(['id' => $id])) {
            return $this->updatedResponse();
        }

        return $this->response->errorBadRequest();
    }
}
