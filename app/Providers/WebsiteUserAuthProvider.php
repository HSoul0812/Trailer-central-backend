<?php
namespace App\Providers;
use App\Models\Website\User\WebsiteUserToken;
use Dingo\Api\Auth\Provider\Authorization;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class WebsiteUserAuthProvider extends Authorization {
    public function authenticate(\Illuminate\Http\Request $request, \Dingo\Api\Routing\Route $route)
    {
        if ($request->header('user-access-token')) {
            $accessToken = WebsiteUserToken::where('access_token', $request->header('user-access-token'))->first();
            if ($accessToken && $accessToken->user) {
                return $accessToken->user;
            }
        }
        throw new UnauthorizedHttpException('Unable to authenticate access token');
    }

    public function getAuthorizationMethod()
    {
        return 'website_auth';
    }
}
