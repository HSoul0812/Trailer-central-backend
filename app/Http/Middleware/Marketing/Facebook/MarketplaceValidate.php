<?php

namespace App\Http\Middleware\Integration\Facebook;

use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ValidRoute;
use App\Models\Marketing\Facebook\Marketplace;

class MarketplaceValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Facebook Marketplace Integration does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::ID_PARAM] = function ($data) {
            $marketplace = Marketplace::find($data);
            if (empty($marketplace)) {
                return false;
            }

            // Get Auth
            if (Auth::user()->dealer_id !== $marketplace->dealer_id) {
                return false;
            }
            
            return true;
        };
    }
}
