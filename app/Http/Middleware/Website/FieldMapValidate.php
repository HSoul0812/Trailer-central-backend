<?php

namespace App\Http\Middleware\Website;

use App\Http\Middleware\ValidRoute;
use App\Models\Website\Forms\FieldMap;

class FieldMapValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Field Map does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::ID_PARAM] = function($data) {            
            return empty(FieldMap::find($data));
        };
    }
}
