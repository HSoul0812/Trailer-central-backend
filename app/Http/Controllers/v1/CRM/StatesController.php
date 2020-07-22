<?php

namespace App\Http\Controllers\v1\CRM;

use App\Http\Controllers\RestfulController;
use App\Models\CRM\Leads\Lead;
use Dingo\Api\Http\Request;
use App\Http\Requests\CRM\GetStatesRequest;

class StatesController extends RestfulController
{
    public function index(Request $request) {
        $request = new GetStatesRequest($request->all());

        if ($request->validate()) {             
            return $this->response->array([
                'data' => Lead::getStates()
            ]);
        }
        
        return $this->response->errorBadRequest();
    }
}
