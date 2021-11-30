<?php

namespace App\Http\Middleware\Dispatch;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ValidRoute;
use App\Models\Marketing\Facebook\Marketplace;

class FacebookValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Facebook Marketplace Integration does not exist.'
        ]
    ];
    
    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];
       
    protected $validator = [];
    
    public function __construct() {
        $this->validator[self::ID_PARAM] = function ($data) {
            $marketplace = Marketplace::find($data);
            if (empty($marketplace)) {
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
        // Get Auth
        $auth = Auth::user();
        if ($auth->name === 'dispatch-fb') {
            return parent::handle($request, $next);
        }

        return response('Valid facebook dispatch token is required.', 403);
    }
}
