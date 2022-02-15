<?php

namespace App\Http\Controllers\v1\Dms\Printer;

use App\Http\Controllers\RestfulController;
use App\Services\Dms\Printer\InstructionsServiceInterface;
use App\Http\Requests\Dms\Printer\GetInstructionRequest;
use Dingo\Api\Http\Request;

class InstructionController extends RestfulController 
{
    /**     
     * @var App\Services\Dms\Printer\InstructionsServiceInterface
     */
    protected $instructionsService;
    
    /**
     * Create a new controller instance.
     *
     * @param  App\Services\Dms\Printer\InstructionsServiceInterface  $instructionsService
     */
    public function __construct(InstructionsServiceInterface $instructionsService)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index']);
        $this->instructionsService = $instructionsService;
    }
    
    public function index(Request $request) 
    {
        $request = new GetInstructionRequest($request->all());
        
        if ($request->validate()) {
            return $this->response->array($this->instructionsService->getPrintInstruction($request->dealer_id, $request->label, $request->barcode_data));
        }
        
        return $this->response->errorBadRequest();
    }
}
