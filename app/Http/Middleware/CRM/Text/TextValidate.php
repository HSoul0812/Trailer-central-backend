<?php

namespace App\Http\Middleware\CRM\Text;

use Closure;
use App\Http\Middleware\ValidRoute;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Interactions\TextLog;

class TextValidate extends ValidRoute {

    const LEAD_ID_PARAM = 'leadId';
    const ID_PARAM = 'id';
    protected $params = [
        self::LEAD_ID_PARAM => [
            'optional' => false,
            'message' => 'CRM Lead does not exist.'
        ],
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Text does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::LEAD_ID_PARAM => 'lead_id',
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::LEAD_ID_PARAM] = function($data) {
            $lead = Lead::find($data);
            if (empty($lead)) {
                return false;
            }

            // Get Auth
            $auth = Auth::user();
            if ($auth->dealer_id !== $lead->dealer_id) {
                return false;
            }

            return true;
        };
        
        $this->validator[self::ID_PARAM] = function ($data) {
            if (empty(TextLog::find($data))) {
                return false;
            }
            
            return true;
        };
    }
}
