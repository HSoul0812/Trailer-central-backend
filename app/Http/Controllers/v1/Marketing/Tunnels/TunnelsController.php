<?php

namespace App\Http\Controllers\v1\Marketing\Tunnels;


use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\User\DealerUserRepositoryInterface;
use App\Services\Common\EncrypterServiceInterface;
use Illuminate\Http\Request;

class TunnelsController extends RestfulControllerV2
{
    public function check(Request $request, DealerUserRepositoryInterface $dealerUserRepo, EncrypterServiceInterface $encrypterService)
    {
        $dealerEmail = $request->get('email', '');
        $dealerPassword = $request->get('password', '');
        if ($dealerEmail === '') {
            abort(404);
        }
        $dealer = $dealerUserRepo->getByDealerEmail($dealerEmail);
        if ($dealer && $dealer->password === $encrypterService->encryptBySalt($dealerPassword, $dealer->salt)) {
            return $this->response->array([
                'dealerId' => $dealer->dealer_id,
                'dealerName' => $dealer->name
            ]);
        } else {
            abort(404);
        }
    }
}
