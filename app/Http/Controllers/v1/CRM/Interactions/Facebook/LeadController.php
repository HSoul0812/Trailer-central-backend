<?php

namespace App\Http\Controllers\v1\CRM\Interactions\Facebook;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Interactions\Facebook\BulkUpdateLeadRequest;
use App\Repositories\CRM\Leads\FacebookRepository;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

/**
 * Class LeadController
 * @package App\Http\Controllers\v1\CRM\Interactions\Facebook
 */
class LeadController extends RestfulControllerV2
{
    /**
     * @var FacebookRepository
     */
    private $facebookRepository;

    /**
     * @param FacebookRepository $facebookRepository
     */
    public function __construct(FacebookRepository $facebookRepository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['bulkUpdate']);

        $this->facebookRepository = $facebookRepository;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function bulkUpdate(Request $request): Response
    {
        $request = new BulkUpdateLeadRequest($request->all());

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $this->facebookRepository->bulkUpdateFbLead($request->all());

        return $this->updatedResponse();
    }
}
