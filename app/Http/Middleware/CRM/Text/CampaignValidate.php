<?php

namespace App\Http\Middleware\CRM\Text;

use Closure;
use App\Http\Middleware\ValidRoute;
use App\Models\CRM\Text\Campaign;

class CampaignValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Text Campaign does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::ID_PARAM] = function ($data) {
            $campaign = Campaign::find($data);
            if (empty($campaign)) {
                return false;
            }

            // Get Auth
            if (Auth::user()->dealer_id !== $campaign->newDealerUser->dealer_id) {
                return false;
            }
            
            return true;
        };
    }
}
