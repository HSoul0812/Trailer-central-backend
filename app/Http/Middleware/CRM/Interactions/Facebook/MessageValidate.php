<?php

namespace App\Http\Middleware\CRM\Interactions\Facebook;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ValidRoute;
use App\Models\CRM\Leads\Lead;

class MessageValidate extends ValidRoute {

    const LEAD_ID_PARAM = 'leadId';
    protected $params = [
        self::LEAD_ID_PARAM => [
            'optional' => true,
            'message' => 'Lead does not exist.'
        ]
    ];

    protected $appendParams = [
        self::LEAD_ID_PARAM => 'lead_id'
    ];

    protected $validator = [];

    public function __construct() {
        $this->validator[self::LEAD_ID_PARAM] = function ($data) {
            $lead = Lead::find($data);
            if (empty($lead)) {
                return false;
            }

            // Get Auth
            if (Auth::user()->newDealerUser->id !== $lead->dealer_id) {
                return false;
            }
            
            return true;
        };
    }
}
