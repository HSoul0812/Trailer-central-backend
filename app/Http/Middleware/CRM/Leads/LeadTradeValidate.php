<?php

namespace App\Http\Middleware\CRM\Leads;

use App\Http\Middleware\ValidRoute;
use App\Models\CRM\Leads\Lead;
use App\Models\CRM\Leads\LeadTrade;
use Illuminate\Support\Facades\Auth;

class LeadTradeValidate extends ValidRoute {

    const LEAD_PARAM = 'leadId';
    const ID_PARAM = 'id';
    protected $params = [
        self::LEAD_PARAM => [
            'optional' => true,
            'message' => 'Lead does not exist.'
        ],
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Lead Trade does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::LEAD_PARAM => self::LEAD_PARAM,
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::LEAD_PARAM] = function ($data) {
            $lead = Lead::find($data);
            if (empty($lead)) {
                return false;
            }

            // Lead Must Belong to Dealer!
            if (Auth::user()->dealer_id !== $lead->dealer_id) {
                return false;
            }
            
            return true;
        };

        $this->validator[self::ID_PARAM] = function ($data) {
            $leadTrade = LeadTrade::find($data);
            if (empty($leadTrade)) {
                return false;
            }

            // Lead Must Belong to Dealer!
            if (Auth::user()->dealer_id !== $leadTrade->lead->dealer_id) {
                return false;
            }
            
            return true;
        };
    }
}
