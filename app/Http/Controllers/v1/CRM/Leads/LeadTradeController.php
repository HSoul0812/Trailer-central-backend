<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Leads\GetLeadTradesRequest;
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
        $this->middleware('setDealerIdOnRequest')->only(['index']);
        $this->leadTradeRepository = $leadTradeRepository;
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
            return $this->collectionResponse($this->leadTradeRepository->getAll($request->all()), new LeadTradeTransformer());
        }

        return $this->response->errorBadRequest();
    }
}
