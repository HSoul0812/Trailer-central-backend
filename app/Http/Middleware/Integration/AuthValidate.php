<?php

namespace App\Http\Middleware\Integration;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ValidRoute;
use App\Models\Integration\Auth\AccessToken;

class AuthValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Integration Access Token does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::ID_PARAM] = function ($data) {
            $token = AccessToken::find($data);
            if (empty($token)) {
                return false;
            }

            // Get Auth
            if (Auth::user()->dealer_id !== $token->newDealerUser->id) {
                return false;
            }
            
            return true;
        };
    }
}
