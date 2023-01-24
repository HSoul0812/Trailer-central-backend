<?php

namespace App\Http\Controllers\v1\Marketing\Tunnels;


use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\User\DealerUserRepositoryInterface;
use Illuminate\Http\Request;

class TunnelsController extends RestfulControllerV2
{
    public function check(Request $request, DealerUserRepositoryInterface $dealerUserRepo)
    {
        $dealerEmail = $request->get('email', '');
        if ($dealerEmail === '') {
            abort(404);
        }
        $dealer = $dealerUserRepo->getByDealerEmail($dealerEmail);
        if ($dealer) {
            return $this->response->array([
                'dealerId' => $dealer->dealer_id,
                'dealerName' => $dealer->name
            ]);
        } else {
            abort(404);
        }
    }
}
