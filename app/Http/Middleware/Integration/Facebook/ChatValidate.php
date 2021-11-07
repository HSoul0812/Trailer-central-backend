<?php

namespace App\Http\Middleware\Integration\Facebook;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Http\Middleware\ValidRoute;
use App\Models\Integration\Facebook\Chat;

class ChatValidate extends ValidRoute {

    const ID_PARAM = 'id';
    protected $params = [
        self::ID_PARAM => [
            'optional' => true,
            'message' => 'Facebook Chat does not exist.'
        ]
    ];

    protected $appendParams = [
        self::ID_PARAM => self::ID_PARAM
    ];

    protected $validator = [];

    public function __construct() {
        $this->validator[self::ID_PARAM] = function ($data) {
            $chat = Chat::find($data);
            if (empty($chat)) {
                return false;
            }

            // Get Auth
            if (Auth::user()->newDealerUser->user_id !== $chat->user_id) {
                return false;
            }

            // Sales Person ID Must Match if Exists!
            if(!empty(Auth::user()->sales_person->id)) {
                if (Auth::user()->sales_person->id !== $chat->sales_person_id) {
                    return false;
                }
            }
            
            return true;
        };
    }
}
