<?php

namespace App\Http\Controllers\v1\CRM\Documents;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Documents\DealerDocumentsRequest;
use App\Repositories\CRM\Documents\DealerDocumentsRepositoryInterface;
use App\Transformers\CRM\Documents\DealerDocumentsTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Contracts\Container\BindingResolutionException;

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
     * @param DealerDocumentsRepositoryInterface $dealerDocumentsRepository
     */
    public function __construct(DealerDocumentsRepositoryInterface $dealerDocumentsRepository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index']);

        $this->dealerDocumentsRepository = $dealerDocumentsRepository;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException|BindingResolutionException
     */
    public function index(Request $request): Response
    {
        $request = new DealerDocumentsRequest($request->all());
        $requestData = $request->all();

        if ($request->validate()) {
            $transformer = app()->make(DealerDocumentsTransformer::class);
            return $this->collectionResponse($this->dealerDocumentsRepository->getAll($requestData), $transformer);
        }

        return $this->response->errorBadRequest();
    }
}
