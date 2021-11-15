<?php

namespace App\Http\Controllers\v1\CRM\Interactions;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Interactions\BulkUpdateEmailHistoryRequest;
use App\Repositories\CRM\Interactions\EmailHistoryRepositoryInterface;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class EmailHistoryController extends RestfulControllerV2
{
    /**
     * @var EmailHistoryRepositoryInterface
     */
    private $emailHistoryRepository;

    /**
     * @param EmailHistoryRepositoryInterface $emailHistoryRepository
     */
    public function __construct(EmailHistoryRepositoryInterface $emailHistoryRepository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['bulkUpdate']);

        $this->emailHistoryRepository = $emailHistoryRepository;
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
        $request = new BulkUpdateEmailHistoryRequest($request->all());

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $this->emailHistoryRepository->bulkUpdate($request->all());

        return $this->updatedResponse();
    }
}
