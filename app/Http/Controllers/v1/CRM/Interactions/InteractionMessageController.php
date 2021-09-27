<?php

namespace App\Http\Controllers\v1\CRM\Interactions;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Interactions\SearchInteractionMessages;
use Dingo\Api\Http\Request;
use App\Repositories\CRM\Interactions\InteractionMessageRepositoryInterface;
use App\Transformers\CRM\Interactions\InteractionMessageTransformer;
use Dingo\Api\Http\Response;

/**
 * Class InteractionLeadController
 * @package App\Http\Controllers\v1\CRM\Interactions
 */
class InteractionMessageController extends RestfulControllerV2
{
    /**
     * @var InteractionMessageRepositoryInterface
     */
    private $interactionLeadRepository;

    /**
     * @param InteractionMessageRepositoryInterface $interactionLeadRepository
     */
    public function __construct(InteractionMessageRepositoryInterface $interactionLeadRepository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['search']);

        $this->interactionLeadRepository = $interactionLeadRepository;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function search(Request $request): Response
    {
        $request = new SearchInteractionMessages($request->all());

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $data = $this->interactionLeadRepository->search($request->all());
        $paginator = $this->interactionLeadRepository->getPaginator();

        return $this->collectionResponse($data, new InteractionMessageTransformer(), $paginator);
    }
}
