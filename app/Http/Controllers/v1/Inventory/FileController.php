<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Inventory\Files\CreateFileRequest;
use App\Http\Requests\Inventory\Files\DeleteFilesRequest;
use App\Services\Inventory\InventoryServiceInterface;
use App\Transformers\Inventory\FileTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

/**
 * Class FileController
 * @package App\Http\Controllers\v1\Inventory
 */
class FileController extends RestfulControllerV2
{
    /**
     * @var InventoryServiceInterface
     */
    protected $inventoryService;

    public function __construct(InventoryServiceInterface $inventoryService)
    {
        $this->middleware('setDealerIdOnRequest')->only(['create', 'bulkDestroy']);
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
        $createFileRequest = new CreateFileRequest(['inventory_id' => $id] + $request->all());

        if (!$createFileRequest->validate() || !($data = $this->inventoryService->createFile($id, $createFileRequest->all()))) {
            return $this->response->errorBadRequest();
        }

        return $this->itemResponse($data, new FileTransformer());
    }

    /**
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function bulkDestroy(int $id, Request $request): Response
    {
        $bulkRequest = new DeleteFilesRequest(['inventory_id' => $id] + $request->all());

        if ($bulkRequest->validate() && $this->inventoryService->fileBulkDelete($bulkRequest->inventory_id)) {
            return $this->deletedResponse();
        }

        $this->response->errorBadRequest();
    }
}
