<?php

namespace App\Http\Controllers\v1\Marketing\Tunnels;


use App\Http\Controllers\RestfulControllerV2;
use App\Models\User\User;
use Illuminate\Http\Request;

class TunnelsController extends RestfulControllerV2
{
    public function check(Request $request)
    {
        $dealer = User::where('email', '=', $request->get('email', ''))->firstOrFail();
        return $this->response->array([
            'dealerId' => $dealer->dealer_id,
            'dealerName' => $dealer->name
        ]);
    }
}
