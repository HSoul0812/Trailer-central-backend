<?php

namespace App\Http\Controllers\v1\Website\Log;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Website\Log\CreateLogRequest;
use Dingo\Api\Http\Request;
use App\Services\Website\Log\LogServiceInterface;

/**
 * TODO: Needs to be protected via an API key
 */
class LogController extends RestfulController
{    
    /**
     * @var App\Services\Website\Log\LogServiceInterface 
     */
    protected $logService;
    
    public function __construct(LogServiceInterface $logService) {
        $this->logService = $logService;
    }
    
    public function create(Request $request)
    {
        $request = new CreateLogRequest($request->all());

        if ($request->validate()) {
            if ($this->logService->log($request->message)) {
                return $this->response->created();
            }            
        }

        return $this->response->errorBadRequest();
    }
}
