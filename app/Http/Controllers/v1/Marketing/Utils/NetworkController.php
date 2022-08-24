<?php

namespace App\Http\Controllers\v1\Marketing\Utils;

use App\Helpers\IpHelper;
use App\Http\Controllers\RestfulControllerV2;
use Illuminate\Http\Request;

class NetworkController extends RestfulControllerV2
{
    /**
     * IpHelper
     */
    private $ipHelper;


    /**
     * Create a new controller instance.
     */
    public function __construct() {
        $this->ipHelper = new IpHelper();
    }

    /**
     * Get IP For 
     * 
     * @param Request $request
     * @return type
     */
    public function getIp(Request $request) {
        // Return Response IP
        return $this->response->array([
            'ip' => $this->ipHelper->findIp() ?? $request->ip()
        ]);
    } 
}
