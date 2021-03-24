<?php

namespace App\Http\Middleware\CRM\User;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ValidRoute;
use App\Models\CRM\User\SalesPerson;

class SalesPersonValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Sales Person does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::ID_PARAM] = function ($data) {
            $sales = SalesPerson::find($data);
            if (empty($sales)) {
                return false;
            }

            // Get Auth
            if (Auth::user()->dealer_id !== $sales->newDealerUser->id) {
                return false;
            }
            
            return true;
        };
    }
}
