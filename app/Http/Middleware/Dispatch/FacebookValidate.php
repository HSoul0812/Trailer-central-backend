<?php

namespace App\Http\Middleware\Dispatch;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ValidRoute;
use App\Models\Marketing\Facebook\Marketplace;
use App\Models\User\AuthToken;

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
        $auth = Auth::user();
        var_dump($auth);
        die;
        if ($request->header('access-token')) {
            $accessToken = AuthToken::where('access_token', $request->header('access-token'))->first();
            if ($accessToken && $accessToken->user->name === 'dispatch-facebook') {
                return parent::handle($request, $next);
            }
        }

        return response('Valid facebook dispatch token is required.', 403);
    }
}
