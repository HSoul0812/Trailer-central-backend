<?php

namespace App\Http\Controllers\v1\Bulk\Inventory;

use Dingo\Api\Http\Request;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Bulk\Inventory\CreateBulkUploadRequest;
use App\Repositories\Bulk\Inventory\BulkUploadRepository;

use App\Http\Requests\Bulk\Inventory\GetBulkUploadRequest;
use App\Transformers\Bulk\Inventory\BulkUploadTransformer;

/**
 * Class BulkUploadController
 * @package App\Http\Controllers\v1\Bulk\Inventory
 */
class BulkUploadController extends RestfulControllerV2
{

    protected $bulkUploads;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'create']);
        $this->bulkUploads = \app('App\Repositories\Bulk\Inventory\BulkUploadRepository');
    }

    /**
     * @param Request $request
     * @return Response
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
