<?php

namespace App\Http\Middleware\Website;

use App\Http\Middleware\ValidRoute;
use App\Models\Website\Forms\FieldMap;

class FieldMapValidate extends ValidRoute {

    const TYPE_PARAM = 'type';
    protected $params = [
        self::TYPE_PARAM => [
            'optional' => true,
            'message' => 'Field Map type does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::TYPE_PARAM => self::TYPE_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::TYPE_PARAM] = function($data) {            
            if (!isset(FieldMap::MAP_TYPES[$data])) {
                return false;
            }

            return true;
        };
    }
}
