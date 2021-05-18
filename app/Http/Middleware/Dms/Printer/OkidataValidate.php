<?php

namespace App\Http\Middleware\Dms\Printer;

use App\Http\Middleware\ValidRoute;
use App\Models\CRM\Dms\Printer\Okidata;

class OkidataValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Okidata Form does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::ID_PARAM] = function ($data) {
            $okidata = Okidata::find($data);
            return !empty($okidata->id);
        };
    }
}
