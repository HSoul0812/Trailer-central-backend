<?php

namespace App\Http\Middleware\CRM\Email;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ValidRoute;
use App\Models\CRM\Email\Template;

class TemplateValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Email Template does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::ID_PARAM] = function ($data) {
            $template = Template::find($data);
            if (empty($template)) {
                return false;
            }

            // Get Auth
            if (Auth::user()->dealer_id !== $template->newDealerUser->id) {
                return false;
            }
            
            return true;
        };
    }
}
