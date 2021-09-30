<?php

namespace App\Http\Controllers\v1\CRM\Interactions;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Interactions\SearchInteractionMessagesRequest;
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
     * @OA\Get(
     *     path="/api/leads/interaction-message/search",
     *     description="Retrieve a list of interaction messages",
     *     tags={"Interaction"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Current Page",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort order",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="message_type",
     *         in="query",
     *         description="Type of message. Available values: sms, email, fb",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="The key phrase for searching. Searched in title, lead_first_name, lead_last_name, text",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="query",
     *         in="hidden",
     *         description="Hidden or not",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of interaction messages",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param Request $request
     * @return Response
     *
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function search(Request $request): Response
    {
        $request = new SearchInteractionMessagesRequest($request->all());

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $data = $this->interactionLeadRepository->search($request->all());
        $paginator = $this->interactionLeadRepository->getPaginator();

        return $this->collectionResponse($data, new InteractionMessageTransformer(), $paginator);
    }
}
