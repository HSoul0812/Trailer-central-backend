<?php

namespace App\Http\Controllers\v1\Bulk\Inventory;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Repositories\Bulk\Inventory\BulkUploadRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Bulk\Inventory\CreateBulkUploadRequest;
use App\Http\Requests\Bulk\Inventory\GetBulkUploadRequest;
use App\Transformers\Bulk\Inventory\BulkUploadTransformer;
use Dingo\Api\Http\Response;

/**
 * Class BulkUploadController
 * @package App\Http\Controllers\v1\Bulk\Inventory
 */
class BulkUploadController extends RestfulControllerV2
{
    /**
     * @var BulkUploadRepositoryInterface
     */
    protected $bulkUploads;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BulkUploadRepositoryInterface $bulkUploadRepository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'create']);
        $this->bulkUploads = $bulkUploadRepository;
    }

    /**
     * @param Request $request
     * @return Response|null
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function index(Request $request)
    {
        $request = new GetBulkUploadRequest($request->all());

        if ($request->validate()) {
            return $this->response->paginator($this->bulkUploads->getAll($request->all()), new BulkUploadTransformer);
        }

        return $this->response->errorBadRequest();
    }


    /**
     * @param Request $request
     * @return Response
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function create(Request $request)
    {
        $request = new CreateBulkUploadRequest($request->all());

        if ($request->validate()) {
            return $this->response->item($this->bulkUploads->create($request->all()), new BulkUploadTransformer);
        }

        return $this->response->errorBadRequest();
    }
}
