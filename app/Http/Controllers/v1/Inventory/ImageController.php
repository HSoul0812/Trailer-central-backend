<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Inventory;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Inventory\Images\CreateImageRequest;
use App\Http\Requests\Inventory\Images\DeleteImagesRequest;
use App\Services\Inventory\InventoryServiceInterface;
use App\Transformers\Inventory\InventoryImageTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class ImageController extends RestfulControllerV2
{
    /**
     * @var InventoryServiceInterface
     */
    protected $inventoryService;

    public function __construct(InventoryServiceInterface $inventoryService)
    {
        $this->middleware('setDealerIdOnRequest')->only(['create', 'bulkDestroy']);

        // this permission is inherited from the creation permission,
        // if it would be necessary to have a separate permission for this action, then it should be added here
        $this->middleware('inventory.create.permission')->only(['bulkDestroy']);

        $this->inventoryService = $inventoryService;
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function create(int $id, Request $request): Response
    {
        $createImageRequest = new CreateImageRequest(['inventory_id' => $id] + $request->all());

        if (!$createImageRequest->validate() || !($data = $this->inventoryService->createImage($id, $createImageRequest->all()))) {
            return $this->response->errorBadRequest();
        }

        return $this->itemResponse($data, new InventoryImageTransformer());
    }

    /**
     * @OA\Delete(
     *     path="/api/inventory/{id}/images",
     *     description="Bulk delete images of inventory",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Inventory ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="image_ids",
     *                     description="Inventory images IDs",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response="204",
     *         description="Confirms images was deleted",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     )
     * )
     *
     * @param int $id
     * @return Response
     *
     * @throws \Dingo\Api\Exception\ResourceException when there was a bad request
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when there was a bad request
     */
    public function bulkDestroy(int $id, Request $request): Response
    {
        $bulkRequest = new DeleteImagesRequest(['inventory_id' => $id] + $request->all());

        if ($bulkRequest->validate() &&
            $this->inventoryService->imageBulkDelete($bulkRequest->inventory_id, $bulkRequest->image_ids)
        ) {
            return $this->deletedResponse();
        }

        $this->response->errorBadRequest();
    }
}
