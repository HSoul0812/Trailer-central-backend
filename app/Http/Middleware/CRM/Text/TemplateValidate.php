<?php

namespace App\Http\Middleware\CRM\Text;

use Closure;
use App\Http\Middleware\ValidRoute;
use App\Models\CRM\Text\Template;

class TemplateValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Text Template does not exist.'
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
            if (Auth::user()->dealer_id !== $template->newDealerUser->dealer_id) {
                return false;
            }
            
            return true;
        };
    }
}
