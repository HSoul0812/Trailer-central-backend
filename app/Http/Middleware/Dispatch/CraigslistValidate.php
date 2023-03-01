<?php

namespace App\Http\Middleware\Dispatch;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ValidRoute;
use App\Models\User\AuthToken;
use App\Models\User\DealerClapp;

class CraigslistValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Craigslist Dealer does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::ID_PARAM] = function ($data) {
            $clapp = DealerClapp::where('dealer_id', $data)->first();
            if (empty($clapp)) {
                return false;
            }
            
            return true;
        };
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $auth = Auth::user();
        if (!empty($auth) && $auth->name === 'dispatch_craigslist') {
            return parent::handle($request, $next);
        }

        return response('Valid craigslist dispatch token is required.', 403);
    }
}
