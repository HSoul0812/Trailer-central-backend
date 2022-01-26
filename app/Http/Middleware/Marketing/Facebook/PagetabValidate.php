<?php

namespace App\Http\Middleware\Marketing\Facebook;

use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ValidRoute;
use App\Models\Integration\Facebook\Page;

class PagetabValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Facebook Page Tab does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::ID_PARAM] = function ($data) {
            $page = Page::find($data);
            if (empty($page)) {
                return false;
            }

            // Get Auth
            if (Auth::user()->dealer_id !== $page->dealer_id) {
                return false;
            }
            
            return true;
        };
    }
}
