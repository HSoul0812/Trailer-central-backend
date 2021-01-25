<?php

namespace App\Http\Controllers\v1\CRM\Leads;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\CRM\Leads\ImportRepositoryInterface;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\Leads\Import\GetImportsRequest;
use App\Http\Requests\CRM\Leads\Import\UpdateImportRequest;
use App\Http\Requests\CRM\Leads\Import\DeleteImportRequest;
use App\Transformers\CRM\Leads\ImportTransformer;

class LeadImportController extends RestfulControllerV2
{
    protected $leads;
    
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param Repository $leads
     */
    public function __construct(ImportRepositoryInterface $imports)
    {
        $this->middleware('setDealerIdOnRequest');

        $this->imports = $imports;
        $this->transformer = new ImportTransformer;
    }

    /**
     * Get All Imports for Dealer
     * 
     * @param Request $request
     * @return type
     */
    public function index(Request $request) {
        $request = new GetImportsRequest($request->all());
        if ($request->validate()) {
            return $this->response->array($this->imports->getAll($request->all()), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Update Lead Imports for Dealer
     * 
     * @param Request $request
     * @return type
     */
    public function update(Request $request) {
        $request = new UpdateImportRequest($request->all());
        if ($request->validate()) {
            return $this->response->item($this->imports->update($request->all()), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Delete All for Dealer ID
     * 
     * @param Request $request
     * @return success or failure
     */
    public function destroy(Request $request) {
        $request = new DeleteImportRequest($request->all());
        if ($request->validate() && $this->imports->delete($request->all())) {
            return $this->successResponse();
        }
        
        return $this->response->errorBadRequest();
    }
}