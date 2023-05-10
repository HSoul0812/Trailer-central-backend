<?php

namespace App\Http\Middleware\CRM\Report;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ValidRoute;
use App\Models\CRM\Report\Report;

class ReportValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Report does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() 
    {
        $this->validator[self::ID_PARAM] = function ($data) {

            $report = Report::find($data);
            
            return !empty($report) 
                && Auth::user()->newDealerUser->user_id === $report->user_id;
        };
    }
}
