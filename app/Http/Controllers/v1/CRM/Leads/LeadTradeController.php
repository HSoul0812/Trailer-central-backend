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
     * @param LeadTradeRepositoryInterface $leadTradeRepository
     */
    public function __construct(LeadTradeRepositoryInterface $leadTradeRepository)
    {
        $this->leadTradeRepository = $leadTradeRepository;
        $this->transformer = new LeadTradeTransformer();
    }

    /**
     * @param Request $request
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function index(Request $request): Response
    {
        $request = new GetLeadTradesRequest($request->all());

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
    public function show(int $id)
    {
        $request = new GetLeadTradeRequest(['id' => $id]);

        if ($request->validate()) {
            return $this->response->item($this->leadTradeRepository->get(['id' => $id]), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Insert new data into the DB
     *
     * @param Request $request
     */
    public function create(Request $request) {
        $request = new CreateLeadTradeRequest($request->all());

        if ($request->validate()) {
            return $this->response->item($this->leadTradeRepository->create($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Update existing data on the record already in the DB
     *
     * @param int $id
     * @param Request $request
     */
    public function update(int $id, Request $request) {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateLeadTradeRequest($requestData);

        if ($request->validate()) {
            return $this->response->item($this->leadTradeRepository->update($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Delete existing data on the record already in the DB
     *
     * @param int $id
     * @param Request $request
     */
    public function destroy(int $id, Request $request)
    {
        $request = new DeleteLeadTradeRequest(array_merge($request->all(), ['id' => $id]));
        
        if ($request->validate() && $this->leadTradeRepository->delete(['id' => $id]) > 0) {
            return $this->updatedResponse();
        }

        return $this->response->errorBadRequest();
    }
}
