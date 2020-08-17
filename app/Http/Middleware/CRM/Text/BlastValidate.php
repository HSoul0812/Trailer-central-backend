<?php

namespace App\Http\Middleware\CRM\Text;

use Closure;
use App\Http\Middleware\ValidRoute;
use App\Models\CRM\Text\Blast;

class BlastValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Text Blast does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::ID_PARAM] = function ($data) {
            $blast = Blast::find($data);
            if (empty($blast)) {
                return false;
            }
            
            return true;
        };
    }
}
