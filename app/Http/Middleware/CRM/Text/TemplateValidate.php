<?php

namespace App\Http\Middleware\CRM\Text;

use Closure;
use App\Http\Middleware\ValidRoute;
use App\Models\CRM\User\User;
use App\Models\CRM\Text\Template;

class TemplateValidate extends ValidRoute {

    const USER_ID_PARAM = 'userId';
    const ID_PARAM = 'id';
    protected $params = [
        self::USER_ID_PARAM => [
            'optional' => false,
            'message' => 'CRM User does not exist.'
        ],
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Text Template does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::USER_ID_PARAM => 'user_id',
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::USER_ID_PARAM] = function($data) {            
            if (empty(User::find($data))) {
                return false;
            }
            
            return true;
        };
        
        $this->validator[self::ID_PARAM] = function ($data) {
            if (empty(Template::find($data))) {
                return false;
            }
            
            return true;
        };
    }
}
