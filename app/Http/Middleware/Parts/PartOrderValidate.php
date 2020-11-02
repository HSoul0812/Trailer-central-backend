<?php

namespace App\Http\Middleware\Parts;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ValidRoute;
use App\Models\Parts\PartOrder;

class PartOrderValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => false,
            'message' => 'Part Order does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::ID_PARAM => 'order_id'
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::ID_PARAM] = function($data) {
            $order = PartOrder::find($data);
            if (empty($order)) {
                return false;
            }

            // Get Auth
            if (Auth::user()->dealer_id !== $order->dealer_id) {
                return false;
            }

            return true;
        };
    }
}
