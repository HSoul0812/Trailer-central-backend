<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Dms\Customer;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Dms\Customer\CreateInventoryRequest;
use App\Http\Requests\Dms\Customer\DeleteInventoryRequest;
use App\Http\Requests\Dms\Customer\GetByDealerOrCustomerRequest;
use App\Http\Requests\Dms\Customer\GetInventoryRequest;
use App\Repositories\Dms\Customer\InventoryRepositoryInterface;
use App\Transformers\Dms\Customer\CustomerInventoryTransformer;
use Dingo\Api\Exception\ResourceException;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InventoryController extends RestfulController
{
    /** @var InventoryRepositoryInterface */
    protected $inventoryRepository;

    public function __construct(
        InventoryRepositoryInterface $inventoryRepository
    ) {
        // Gets dealer_id from authentication object, and injects it in the request
        $this->middleware('setDealerIdOnRequest');

        $this->inventoryRepository = $inventoryRepository;
    }

    /**
     * @OA\Get(
     *     path="/api/customer/inventory",
     *     description="Retrieve a list of inventories from a list of customers",
     *     tags={"CustomerInventory"},
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
     *         description="Sort order can be: title,-title,vin,-vin,manufacturer,-manufacturer,status,-status",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="customer_condition",
     *         in="query",
     *         description="Condition for customer inventories: (has,-has) which has include only those inventories owned by the customer, and -has include only those inventories not owned by the customer",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="query",
     *         description="Customers ids array to filter by",
     *         required=true,
     *         @OA\Schema(
     *           type="array",
     *           @OA\Items(type="integer"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of inventories units",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     )
     * )
     *
     * @param Request $request
     * @return Response
     *
     * @throws ResourceException when there were some validation error
     * @throws HttpException when the object does not belong to the current logged in dealer,
     *                       or there were some bad request
     */
    public function index(Request $request): Response
    {
        return $this->getAll($request);
    }

    /**
     * @OA\Get(
     *     path="/api/customer/{customer_id}/inventory",
     *     description="Retrieve a list of inventories from a single customer",
     *     tags={"CustomerInventory"},
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
     *         description="Sort order can be: title,-title,vin,-vin,manufacturer,-manufacturer,status,-status",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="customer_condition",
     *         in="query",
     *         description="Condition for customer inventories: (has,-has) which has include only those inventories owned by the customer, and -has include only those inventories not owned by the customer",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="query",
     *         description="Customers id to filter by",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of inventories units",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     )
     * )
     *
     * @param int $customer_id
     * @param Request $request
     * @return Response
     *
     * @throws ResourceException when there were some validation error
     * @throws HttpException when the object does not belong to the current logged in dealer,
     *                       or there were some bad request
     */
    public function getAllByCustomer(int $customer_id, Request $request): Response
    {
        $request->offsetSet('customer_id', [$customer_id]);

        return $this->getAll($request);
    }

    /**
     * @OA\Delete(
     *     path="/user/customers/{customer_id}/inventory",
     *     tags={"CustomerInventory"},
     *     summary="Remove inventories",
     *     description="Remove all desired inventories from a customer",
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         description="The customer ID.",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="customer_inventory_ids",
     *                     description="Customer inventory relation IDs",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="All inventories were remove from desired customer",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="bool",
     *                         description="The response success"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="The response message"
     *                     ),
     *                     example={
     *                         "success": true,
     *                         "message": "All  inventories were remove",
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Some of the inventories was not found, or the customer was not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="error",
     *                         type="bool",
     *                         description="The response error"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="The response message"
     *                     ),
     *                     example={
     *                         "error": true,
     *                         "message": "Resource not found",
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Throwable message",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="error",
     *                         type="bool",
     *                         description="The response success"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="The response message"
     *                     ),
     *                     example={
     *                         "error": true,
     *                         "message": "Throwable message",
     *                     }
     *                 )
     *             )
     *         }
     *      ),
     * )
     *
     * @param int $customer_id
     * @param Request $request
     * @return Response
     *
     * @throws ResourceException when there were some validation error
     * @throws HttpException when the object does not belong to the current logged in dealer,
     *                       or there were some bad request
     * @throws Exception when there were db errors
     */
    public function bulkDestroy(int $customer_id, Request $request): Response
    {
        $request = new DeleteInventoryRequest($request->all());
        $request->offsetSet('customer_id', $customer_id);
        $ids = $request->get('customer_inventory_ids');

        if ($request->validate() && $this->inventoryRepository->bulkDestroy($ids)) {
            return $this->successResponse();
        }

        $this->response->errorBadRequest();
    }

    /**
     * @OA\Post(
     *     path="/user/customers/{customer_id}/inventory",
     *     tags={"CustomerInventory"},
     *     summary="Add inventories",
     *     description="Add an inventory to a customer",
     *     @OA\Parameter(
     *         name="customer_id",
     *         in="path",
     *         description="The customer ID.",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="unit_id",
     *                     description="Customer inventory relation ID",
     *                     type="integer"
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="The inventory was added to desired customer",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="success",
     *                         type="bool",
     *                         description="The response success"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="The response message"
     *                     ),
     *                     example={
     *                         "success": true,
     *                         "message": "The inventory was added to desired customer",
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="The inventory or customer was not found",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="error",
     *                         type="bool",
     *                         description="The response error"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="The response message"
     *                     ),
     *                     example={
     *                         "error": true,
     *                         "message": "Resource not found",
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Throwable message",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="error",
     *                         type="bool",
     *                         description="The response success"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="The response message"
     *                     ),
     *                     example={
     *                         "error": true,
     *                         "message": "Throwable message",
     *                     }
     *                 )
     *             )
     *         }
     *      ),
     * )
     *
     * @param int $customer_id
     * @param Request $request
     * @return Response
     *
     * @throws ResourceException when there were some validation error
     * @throws HttpException when the object does not belong to the current logged in dealer,
     *                       or there were some bad request
     * @throws Exception when there were db errors
     */
    public function attach(int $customer_id, Request $request): Response
    {
        $request = new CreateInventoryRequest($request->all());
        $request->offsetSet('customer_id', $customer_id);

        if ($request->validate() && $this->inventoryRepository->create($request->all())) {
            return $this->successResponse();
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param Request $request
     * @return Response
     *
     * @throws ResourceException when there were some validation error
     * @throws HttpException when the object does not belong to the current logged in dealer,
     *                       or there were some bad request
     */
    protected function getAll(Request $request): Response
    {
        $request = new GetInventoryRequest($request->all());

        if ($request->validate()) {
            return $this->response->paginator(
                $this->inventoryRepository->getAll($request->all(), true),
                new CustomerInventoryTransformer()
            );
        }

        $this->response->errorBadRequest();
    }
}
