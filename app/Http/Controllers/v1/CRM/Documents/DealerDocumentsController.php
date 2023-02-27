<?php

namespace App\Http\Controllers\v1\CRM\Documents;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Documents\GetDealerDocumentsRequest;
use App\Http\Requests\CRM\Documents\CreateDealerDocumentsRequest;
use App\Http\Requests\CRM\Documents\DeleteDealerDocumentRequest;
use App\Repositories\CRM\Documents\DealerDocumentsRepositoryInterface;
use App\Transformers\CRM\Documents\DealerDocumentsTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Contracts\Container\BindingResolutionException;
use App\Services\CRM\Documents\DealerDocumentsServiceInterface;

/**
 * Class DealerDocumentsController
 * @package App\Http\Controllers\v1\CRM\Dealer
 */
class DealerDocumentsController extends RestfulControllerV2
{
    /**
     * @var DealerDocumentsRepositoryInterface
     */
    private $dealerDocumentsRepository;

    /**
     * @var DealerDocumentsServiceInterface
     */
    private $dealerDocumentsService;

    /**
     * @var DealerDocumentsTransformer
     */
    private $transformer;

    /**
     * @param DealerDocumentsRepositoryInterface $dealerDocumentsRepository
     * @param DealerDocumentsServiceInterface $dealerDocumentsService
     */
    public function __construct(
        DealerDocumentsRepositoryInterface $dealerDocumentsRepository,
        DealerDocumentsServiceInterface $dealerDocumentsService
    )
    {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'create', 'destroy']);

        $this->dealerDocumentsRepository = $dealerDocumentsRepository;
        $this->dealerDocumentsService = $dealerDocumentsService;
        $this->transformer = new DealerDocumentsTransformer;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException|BindingResolutionException
     */
    public function index(int $leadId, Request $request): Response
    {
        $request = new GetDealerDocumentsRequest(array_merge(['lead_id' => $leadId], $request->all()));
        $requestData = $request->all();

        if ($request->validate()) {

            return $this->collectionResponse($this->dealerDocumentsRepository->getAll($requestData), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @param int $leadId
     * @param Request $request
     * @return Response
     */
    public function create(int $leadId, Request $request): Response
    {
        $request = new CreateDealerDocumentsRequest(array_merge(['lead_id' => $leadId], $request->all()));
        $requestData = $request->all();

        if ($request->validate()) {

            return $this->collectionResponse($this->dealerDocumentsService->create($requestData), $this->transformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @param int $leadId
     * @param int $documentId
     * @param Request $request
     * @return Response
     */
    public function destroy(int $leadId, int $documentId, Request $request): Response
    {
        $request = new DeleteDealerDocumentRequest(array_merge(['lead_id' => $leadId, 'document_id' => $documentId], $request->all()));
        $requestData = $request->all();

        if ($request->validate() && $this->dealerDocumentsService->delete($requestData)) {

            return $this->updatedResponse();
        }

        return $this->response->errorBadRequest();
    }
}
