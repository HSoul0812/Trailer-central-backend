<?php

namespace App\Http\Middleware\CRM\Leads;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ValidRoute;
use App\Models\CRM\Leads\LeadSource;

class LeadSourceValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Lead Source does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() 
    {
        $this->validator[self::ID_PARAM] = function ($data) {

            $source = LeadSource::find($data);
            
            return !empty($source) 
                && Auth::user()->newDealerUser->user_id === $source->user_id;
        };
    }
}
