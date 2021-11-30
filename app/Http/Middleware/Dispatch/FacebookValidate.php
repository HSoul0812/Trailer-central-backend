<?php

namespace App\Http\Middleware\Dispatch;

use App\Http\Middleware\ValidRoute;
use App\Models\Marketing\Facebook\Marketplace;

class FacebookValidate extends ValidRoute {

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
            
            return true;
        };
    }
}
