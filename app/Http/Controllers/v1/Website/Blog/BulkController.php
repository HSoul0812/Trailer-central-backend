<?php

namespace App\Http\Controllers\v1\Website\Blog;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Http\Requests\Bulk\Blog\CreateBulkUploadRequest;
use App\Transformers\Bulk\Blog\BulkUploadTransformer;

class BulkController extends RestfulController
{

    protected $bulkUploads;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('setDealerIdOnRequest')->only(['create']);
        $this->bulkUploads = \app('App\Repositories\Website\Blog\BulkRepositoryInterface');
    }

    public function create(Request $request)
    {
        $request = new CreateBulkUploadRequest($request->all());
        if ($request->validate()) {
            return $this->response->item($this->bulkUploads->create($request->all()), new BulkUploadTransformer);
        }

        return $this->response->errorBadRequest();
    }

}
