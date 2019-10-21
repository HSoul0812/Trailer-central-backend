<?php

namespace App\Http\Controllers\v1\Bulk\Parts;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Http\Requests\Bulk\Parts\CreateBulkUploadRequest;
use App\Http\Requests\Bulk\Parts\GetBulkUploadsRequest;
use App\Transformers\Bulk\Parts\BulkUploadTransformer;

class BulkUploadController extends RestfulController
{
    
    protected $bulkUploads;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->bulkUploads = \app('App\Repositories\Bulk\Parts\BulkUploadRepository');
    }
    
    public function index(Request $request)
    {
        $request = new GetBulkUploadsRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->paginator($this->bulkUploads->getAll($request->all()), new BulkUploadTransformer);
        }
        
        return $this->response->errorBadRequest();
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
