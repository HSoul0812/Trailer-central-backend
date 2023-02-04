<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Inventory\MassUpdateInventoryRequest;
use App\Http\Requests\Inventory\CreateInventoryRequest;
use App\Http\Requests\Inventory\DeleteInventoryRequest;
use App\Http\Requests\Inventory\ExistsInventoryRequest;
use App\Http\Requests\Inventory\ExportInventoryRequest;
use App\Http\Requests\Inventory\FindByStockRequest;
use App\Http\Requests\Inventory\GetAllInventoryTitlesRequest;
use App\Http\Requests\Inventory\GetInventoryHistoryRequest;
use App\Http\Requests\Inventory\GetInventoryItemRequest;
use App\Http\Requests\Inventory\SearchInventoryRequest;
use App\Http\Requests\Inventory\UpdateInventoryRequest;
use App\Models\Inventory\Inventory;
use App\Repositories\Inventory\InventoryHistoryRepositoryInterface;
use App\Repositories\Inventory\InventoryRepositoryInterface;
use App\Services\ElasticSearch\Cache\InventoryResponseCacheInterface;
use App\Services\ElasticSearch\Cache\ResponseCacheKeyInterface;
use App\Services\Inventory\InventoryServiceInterface;
use App\Transformers\Inventory\InventoryElasticSearchOutputTransformer;
use App\Transformers\Inventory\SaveInventoryTransformer;
use App\Transformers\Inventory\InventoryHistoryTransformer;
use Dingo\Api\Exception\ResourceException;
use Dingo\Api\Http\Request;
use App\Http\Requests\Inventory\GetInventoryRequest;
use App\Transformers\Inventory\InventoryTransformer;
use Dingo\Api\Http\Response;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Services\ElasticSearch\Inventory\InventoryServiceInterface as InventoryElasticSearchServiceInterface;

/**
 * Class InventoryController
 * @package App\Http\Controllers\v1\Inventory
 */
class InventoryController extends RestfulControllerV2
{
    /**
     * @var InventoryServiceInterface
     */
    protected $inventoryService;

    /**
     * @var InventoryRepositoryInterface
     */
    protected $inventoryRepository;

    /**
     * @var InventoryHistoryRepositoryInterface
     */
    protected $inventoryHistoryRepository;

    /**
     * @var InventoryElasticSearchServiceInterface
     */
    protected $inventoryElasticSearchService;

    /**
     * @var ResponseCacheKeyInterface
     */
    protected $responseCacheKey;

    /**
     * @var InventoryResponseCacheInterface
     */
    public $inventoryResponseCache;

    /**
     * Create a new controller instance.
     *
     * @param InventoryServiceInterface $inventoryService
     * @param InventoryRepositoryInterface $inventoryRepository
     * @param InventoryHistoryRepositoryInterface $inventoryHistoryRepository
     * @param InventoryElasticSearchServiceInterface $inventoryElasticSearchService
     * @param InventoryResponseCacheInterface $inventoryResponseCache
     * @param ResponseCacheKeyInterface $responseCacheKey
     */
    public function __construct(
        InventoryServiceInterface              $inventoryService,
        InventoryRepositoryInterface           $inventoryRepository,
        InventoryHistoryRepositoryInterface    $inventoryHistoryRepository,
        InventoryElasticSearchServiceInterface $inventoryElasticSearchService,
        InventoryResponseCacheInterface        $inventoryResponseCache,
        ResponseCacheKeyInterface              $responseCacheKey
    )
    {
        $this->middleware('setDealerIdOnRequest')
            ->only(['index', 'create', 'update', 'destroy', 'exists', 'getAllTitles', 'findByStock', 'massUpdate']);
        $this->middleware('inventory.create.permission')->only(['create', 'update']);

        $this->inventoryService = $inventoryService;
        $this->inventoryRepository = $inventoryRepository;
        $this->inventoryHistoryRepository = $inventoryHistoryRepository;
        $this->inventoryElasticSearchService = $inventoryElasticSearchService;
        $this->responseCacheKey = $responseCacheKey;
        $this->inventoryResponseCache = $inventoryResponseCache;
    }

    /**
     * @OA\Get(
     *     path="/api/inventory",
     *     description="Retrieve a list of inventory",
     *     tags={"Inventory"},
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
     *         description="Sort order can be: price,-price,relevance,title,-title,length,-length",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *   @OA\Parameter(
     *         name="price",
     *         in="query",
     *         description="Inventory price can be in format: [10 TO 100], [10], [10.0 TO 100.0]",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of parts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $request = new GetInventoryRequest($request->all());

        if ($request->validate()) {
            $requestArray = $request->all();
            /**
             * Filter only floored inventories to pay
             * https://crm.trailercentral.com/accounting/floorplan-payment
             */
            $result = $request->filled('only_floorplanned')
                ? $this->inventoryRepository->getFloorplannedInventory($requestArray)
                : $this->inventoryRepository->getAll($requestArray, true, true);

            return $this->response->paginator($result, new InventoryTransformer());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/exists",
     *     description="Checks whether an item exists",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="stock",
     *         in="query",
     *         description="Inventory stock",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a result",
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
     * @throws NoObjectIdValueSetException|NoObjectTypeSetException
     */
    public function exists(Request $request): Response
    {
        $inventoryRequest = new ExistsInventoryRequest($request->all());

        if (!$inventoryRequest->validate()) {
            return $this->response->errorBadRequest();
        }

        $isExists = $this->inventoryRepository->exists($inventoryRequest->all());

        return $this->existsResponse($isExists);
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/{id}",
     *     description="Retrieve a item",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Inventory ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a item",
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
    public function show(int $id, Request $request): Response
    {
        $request = new GetInventoryItemRequest(array_merge(['id' => $id], $request->all()));

        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $data = $this->inventoryRepository->getAndIncrementTimesViewed($request->all());

        $response = $this->itemResponse($data, new InventoryTransformer());

        if (Inventory::isCacheInvalidationEnabled()) {
            $this->inventoryResponseCache->set(
                $this->responseCacheKey->single($data->inventory_id, $data->dealer_id),
                $response->morph('json')->getContent()
            );
        }

        return $response;
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws NoObjectIdValueSetException
     * @throws BindingResolutionException
     * @throws NoObjectTypeSetException
     */
    public function create(Request $request): Response
    {
        $inventoryRequest = new CreateInventoryRequest($request->all());

        $transformer = app()->make(SaveInventoryTransformer::class);
        $inventoryRequest->setTransformer($transformer);

        if (!$inventoryRequest->validate() || !($inventory = $this->inventoryService->create($inventoryRequest->all()))) {
            return $this->response->errorBadRequest();
        }

        return $this->createdResponse($inventory->inventory_id);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     *
     * @throws BindingResolutionException
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function update(int $id, Request $request): Response
    {
        $inventoryRequest = new UpdateInventoryRequest(array_merge($request->all(), ['inventory_id' => $id]));

        $transformer = app()->make(SaveInventoryTransformer::class);
        $inventoryRequest->setTransformer($transformer);

        if (!$inventoryRequest->validate() || !($inventory = $this->inventoryService->update($inventoryRequest->all()))) {
            return $this->response->errorBadRequest();
        }

        return $this->updatedResponse($inventory->inventory_id);
    }

    /**
     * @param Request $request
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function massUpdate(Request $request): Response
    {
        $bulkRequest = new MassUpdateInventoryRequest($request->all());

        if (!$bulkRequest->validate() || !$this->inventoryService->massUpdate($bulkRequest->all())) {
            return $this->response->errorBadRequest();
        }

        return $this->updatedResponse();
    }

    /**
     * @OA\Delete(
     *     path="/api/inventory/{id}",
     *     description="Delete a item",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Inventory ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Confirms part was deleted",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param int $id
     * @return Response
     *
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function destroy(int $id): Response
    {
        $request = new DeleteInventoryRequest(['id' => $id]);

        if ($request->validate() && $this->inventoryService->delete($id)) {
            return $this->successResponse();
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/{inventory_id}/history",
     *     description="Retrieve a list of transactions belong to a inventory unit",
     *     tags={"Inventory"},
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
     *         description="Sort order can be: in:created_at,-created_at,type,-type,subtype,-subtype,customer_name,-customer_name",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         in="query",
     *         description="Search String",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="inventory_id",
     *         in="query",
     *         description="Inventory identifier",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="query",
     *         description="Customer identifier",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of transactions belong to a inventory unit",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param int $inventoryId
     * @param Request $request
     * @return Response
     *
     * @throws ResourceException when there were some validation error
     * @throws HttpException when the object does not belong to the current logged-in dealer,
     *                       or there were some bad request
     * @throws Exception when there were db errors
     */
    public function history(int $inventoryId, Request $request): Response
    {
        $request = new GetInventoryHistoryRequest(
            array_merge(['inventory_id' => $inventoryId], $request->all())
        );

        if ($request->validate()) {
            return $this->response->paginator(
                $this->inventoryHistoryRepository->getAll($request->all(), true),
                new InventoryHistoryTransformer()
            );
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param int $inventoryId
     * @param Request $request
     * @return Response
     */
    public function deliveryPrice(int $inventoryId, Request $request): Response
    {
        $toZipcode = $request->input('tozip');
        return $this->response->array([
            'response' => [
                'status' => 'success',
                'fee' => $this->inventoryService->deliveryPrice($inventoryId, $toZipcode)
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/get_all_titles",
     *     description="Retrieve a list of inventory without defaults",
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of inventory titles",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function getAllTitles(Request $request)
    {
        $request = new GetAllInventoryTitlesRequest($request->all());

        if ($request->validate()) {
            return $this->response->array(
                $this->inventoryService->getInventoriesTitle($request->all())
            );
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Get(
     *     path="/api/inventory/{id}/export",
     *     description="Exports an inventory",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Inventory ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns an inventory exported in a given format",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param int $inventoryId
     * @param Request $request
     * @return Response
     */
    public function export(int $inventoryId, Request $request): Response
    {
        $inventoryExportRequest = new ExportInventoryRequest($request->all() + ['inventory_id' => $inventoryId]);

        if (!$inventoryExportRequest->validate()) {
            $this->response->errorBadRequest();
        }

        return $this->response->array([
            'response' => [
                'status' => 'success',
                'url' => $this->inventoryService->export($inventoryExportRequest->get('inventory_id'), $inventoryExportRequest->get('format'))
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return Response|void
     *
     * @throws ResourceException when there were some validation error
     */
    public function search(Request $request): Response
    {
        $searchRequest = new SearchInventoryRequest($request->all());

        if ($searchRequest->validate()) {

            $result = $this->inventoryElasticSearchService->search(
                $searchRequest->dealerIds(),
                $searchRequest->terms(),
                $searchRequest->geolocation(),
                $searchRequest->sort(),
                $searchRequest->pagination(),
                $searchRequest->getESQuery()
            );

            $response = $this->response
                ->collection($result->hints, new InventoryElasticSearchOutputTransformer())
                ->addMeta('aggregations', $result->aggregations)
                ->addMeta('total', $result->total);
            if ($searchRequest->getESQuery()) {
                $response->addMeta('x_qa_req', $result->getEncodedESQuery());
            }

            //Cache only if there are results
            if (Inventory::isCacheInvalidationEnabled() && $result->hints->count()) {
                $this->inventoryResponseCache->set(
                    $this->responseCacheKey->collection($searchRequest->requestId(), $result),
                    $response->morph('json')->getContent()
                );
            }
            return $response;
        }

        $this->response->errorBadRequest();
    }

    /**
     * @throws NoObjectTypeSetException
     * @throws NoObjectIdValueSetException
     */
    public function findByStock(string $stock, Request $request)
    {
        $findByStockRequest = new FindByStockRequest($request->all() + ['stock' => $stock]);

        $findByStockRequest->validate();

        $data = $this->inventoryRepository->findByStock(
            $findByStockRequest->input('dealer_id'),
            $stock
        );

        return $this->itemResponse($data, new InventoryTransformer());
    }
}
