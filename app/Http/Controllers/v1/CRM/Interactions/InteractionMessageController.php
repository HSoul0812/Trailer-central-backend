<?php

namespace App\Http\Controllers\v1\CRM\Interactions;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\Interactions\Message\BulkUpdateRequest;
use App\Http\Requests\CRM\Interactions\Message\SearchCountOfRequest;
use App\Http\Requests\CRM\Interactions\Message\SearchRequest;
use App\Http\Requests\CRM\Interactions\Message\UpdateRequest;
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
    private $interactionMessageRepository;

    /**
     * @param InteractionMessageRepositoryInterface $interactionMessageRepository
     */
    public function __construct(InteractionMessageRepositoryInterface $interactionMessageRepository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['search', 'update', 'bulkUpdate', 'searchCountOf']);

        $this->interactionMessageRepository = $interactionMessageRepository;
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
     *         name="hidden",
     *         in="query",
     *         description="Hidden or not",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="dispatched",
     *         in="query",
     *         description="Dispathced or not",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="latest_messages",
     *         in="query",
     *         description="Get only latest messages grouped by a lead",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort order can be: date_sent,-date_sent",
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
        $request = new SearchRequest($request->all());

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $data = $this->interactionMessageRepository->search($request->all());
        $paginator = $this->interactionMessageRepository->getPaginator();

        return $this->collectionResponse($data, new InteractionMessageTransformer(), $paginator);
    }

    /**
     *  @OA\Get(
     *     path="/api/leads/interaction-message/search/count-of/{groupBy}",
     *     description="Retrieve a list of interaction messages",
     *     tags={"Interaction"},
     *     @OA\Parameter(
     *         name="group_by",
     *         in="path",
     *         description="Group by",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="hidden",
     *         in="query",
     *         description="Hidden or not",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="dispatched",
     *         in="query",
     *         description="Dispathced or not",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="is_read",
     *         in="query",
     *         description="Read or not",
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
     * @param string $groupBy
     * @param Request $request
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function searchCountOf(string $groupBy, Request $request): Response
    {
        $params = array_merge($request->all(), ['group_by' => $groupBy]);

        $request = new SearchCountOfRequest($params);

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $data = $this->interactionMessageRepository->searchCountOf($request->all());

        return $this->response->array(['data' => $data]);
    }

    /**
     * @OA\Get(
     *     path="/api/leads/interaction-message/{id}",
     *     description="Retrieve a list of interaction messages",
     *     tags={"Interaction"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Interaction Message ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="hidden",
     *         in="query",
     *         description="Hidden or not",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns interaction message id",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function update(int $id, Request $request): Response
    {
        $request = new UpdateRequest(array_merge(['id' => $id], $request->all()));

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $model = $this->interactionMessageRepository->update($request->all());

        return $this->updatedResponse($model->id);
    }

    /**
     * @OA\Get(
     *     path="/api/leads/interaction-message/{id}",
     *     description="Retrieve a list of interaction messages",
     *     tags={"Interaction"},
     *     @OA\Parameter(
     *         name="ids",
     *         in="path",
     *         description="Interaction Message IDs",
     *         required=true,
     *         @OA\Schema(type="array")
     *     ),
     *     @OA\Parameter(
     *         name="hidden",
     *         in="query",
     *         description="Hidden or not",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="is_read",
     *         in="query",
     *         description="Read or not",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns interaction message id",
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
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function bulkUpdate(Request $request): Response
    {
        $request = new BulkUpdateRequest($request->all());

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $this->interactionMessageRepository->bulkUpdate($request->all());

        return $this->updatedResponse();
    }
}
