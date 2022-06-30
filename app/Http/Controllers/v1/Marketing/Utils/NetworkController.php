<?php

namespace App\Http\Controllers\v1\Marketing\Utils;

use App\Http\Controllers\RestfulControllerV2;
use Illuminate\Http\Request;

class NetworkController extends RestfulControllerV2
{
    public function getIp(Request $request) {
        return $this->response->array([
            'ip' => $request->ip()
        ]);
        $request->ip();
    } 
}
