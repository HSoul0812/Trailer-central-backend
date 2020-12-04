<?php

namespace App\Http\Middleware\Integration\Facebook;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ValidRoute;
use App\Models\Integration\Facebook\Catalog;

class CatalogValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Facebook Catalog does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::ID_PARAM] = function ($data) {
            $catalog = Catalog::find($data);
            if (empty($catalog)) {
                return false;
            }

            // Get Auth
            if (Auth::user()->dealer_id !== $catalog->dealer_id) {
                return false;
            }
            
            return true;
        };
    }
}
