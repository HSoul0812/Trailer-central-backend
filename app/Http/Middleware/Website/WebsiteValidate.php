<?php

namespace App\Http\Middleware\Website;

use Closure;
use App\Http\Middleware\ValidRoute;
use App\Models\Website\Website;
use App\Models\Website\Blog\Post;
use App\Models\Website\PaymentCalculator\Settings;

class WebsiteValidate extends ValidRoute {
    
    const WEBSITE_ID_PARAM = 'websiteId';
    const ID_PARAM = 'id';
    protected $params = [
        self::WEBSITE_ID_PARAM => [
            'optional' => false,
            'message' => 'Website does not exist.'
        ],
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Settings does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::WEBSITE_ID_PARAM => 'website_id',
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::WEBSITE_ID_PARAM] = function($data) {            
            if (empty(Website::find($data))) {
                return false;
            }
            
            return true;
        };
        
        $this->validator[self::ID_PARAM] = function ($data) {
            if (empty(Settings::find($data))) {
                return false;
            }
            
            return true;
        };
    }
}
