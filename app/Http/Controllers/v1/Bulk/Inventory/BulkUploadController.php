<?php

namespace App\Http\Controllers\v1\Bulk\Inventory;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Requests\Bulk\Inventory\GetBulkUploadRequest;
use App\Repositories\Bulk\Inventory\BulkUploadRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Bulk\Inventory\CreateBulkUploadRequest;
use App\Http\Requests\Bulk\Inventory\GetBulkUploadsRequest;
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
    protected $repository;

    /**
     * @var BulkUploadRepositoryInterface
     */
    protected $transfomer;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        BulkUploadRepositoryInterface $bulkUploadRepository,
        BulkUploadTransformer $bulkUploadTransformer
    ) {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'create', 'show']);
        $this->repository = $bulkUploadRepository;
        $this->transfomer = $bulkUploadTransformer;
    }

    /**
     * @param Request $request
     * @return Response|null
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function index(Request $request)
    {
        $request = new GetBulkUploadsRequest($request->all());

        if ($request->validate()) {
            return $this->response->paginator($this->repository->getAll($request->all()), $this->transfomer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Display data about the record in the DB
     *
     * @param int $id
     * @return Response|null
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function show(int $id)
    {
        $request = new GetBulkUploadRequest(['id' => $id]);

        if ($request->validate()) {
            return $this->response->item($this->repository->get(['id' => $id]), $this->transfomer);
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
            return $this->response->item($this->repository->create($request->all()), $this->transfomer);
        }

        return $this->response->errorBadRequest();
    }
}
