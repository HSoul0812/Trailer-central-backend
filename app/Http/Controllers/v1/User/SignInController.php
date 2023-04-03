<?php

namespace App\Http\Controllers\v1\User;

use App\Exceptions\User\TooLongPasswordException;
use App\Http\Controllers\RestfulController;
use App\Models\User\UserAuthenticatable;
use App\Repositories\User\UserRepositoryInterface;
use App\Services\User\PasswordResetServiceInterface;
use App\Http\Requests\User\FinishPasswordResetRequest;
use App\Http\Requests\User\SignInRequest;
use App\Http\Requests\User\StartPasswordResetRequest;
use App\Models\User\AuthToken;
use App\Http\Requests\User\GetDetailsRequest;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use App\Transformers\User\UserSignInTransformer;
use App\Transformers\User\UserTransformer;
use App\Http\Requests\User\CheckAdminPasswordRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Repositories\User\DealerPasswordResetRepositoryInterface;
use App\Models\User\User;
use Grosv\LaravelPasswordlessLogin\LoginUrl;
use Grosv\LaravelPasswordlessLogin\PasswordlessLoginService;

class SignInController extends RestfulController
{

    protected $users;

    protected $passwordResetService;

    protected $transformer;

    /**
     *
     * @var DealerPasswordResetRepositoryInterface
     */
    protected $passwordResetRepo;

    public function __construct(UserRepositoryInterface                $userRepo,
                                PasswordResetServiceInterface          $passwordResetService,
                                DealerPasswordResetRepositoryInterface $passwordResetRepo)
    {
        $this->middleware('setDealerIdOnRequest')->only([
            'updatePassword',
            'checkAdminPassword',
        ]);

        $this->users = $userRepo;
        $this->passwordResetService = $passwordResetService;
        $this->passwordResetRepo = $passwordResetRepo;
        $this->transformer = new UserSignInTransformer();
    }

    public function details(Request $request)
    {
        $accessToken = $request->header('access-token');
        $request = new GetDetailsRequest($request->all());
        if ($request->validate()) {
            $authToken = AuthToken::where('access_token', $accessToken)->firstOrFail();
            $response = $this->response->item($authToken->user, new UserTransformer());

            $response->addMeta('major_units_link', config('app.new_design_crm_url') . $authToken->user->getCrmLoginUrl('/bill-of-sale'))
                ->addMeta('service_link', config('app.new_design_crm_url') . $authToken->user->getCrmLoginUrl('/repair-orders'))
                ->addMeta('parts_link', config('app.new_design_crm_url') . $authToken->user->getCrmLoginUrl('/pos-reports'))
                ->addMeta('f_and_i', config('app.new_design_crm_url') . $authToken->user->getCrmLoginUrl('/fandi/financing'))
                ->addMeta('back_office', config('app.new_design_crm_url') . $authToken->user->getCrmLoginUrl('/accounting'))
                ->addMeta('crm', config('app.new_design_crm_url') . $authToken->user->getCrmLoginUrl());

            return $response;
        }

        return $this->response->errorBadRequest();
    }

    public function signIn(Request $request)
    {
        $request = new SignInRequest($request->all());
        if ($request->validate()) {
            try {
                return $this->response->item($this->users->findUserByEmailAndPassword($request->email, $request->password), $this->transformer);
            } catch (\Exception $ex) {
                return $this->response->errorBadRequest();
            }
        }
        return $this->response->errorBadRequest();
    }

    public function initPasswordReset(Request $request)
    {
        $request = new StartPasswordResetRequest($request->all());
        if ($request->validate()) {
            try {
                $this->passwordResetService->initReset($request->email);
            } catch (\Exception $ex) {
                // Return created anyway
            }
            return $this->response->created();

        }
        return $this->response->errorBadRequest();
    }

    public function finishPasswordReset(Request $request)
    {
        $request = new FinishPasswordResetRequest($request->all());
        if ($request->validate()) {
            try {
                $this->passwordResetService->finishReset($request->code, $request->password, $request->current_password);
            } catch (TooLongPasswordException $ex) {
                // the request validation is preventing this, but just in case anyone remove that rule
                // we're going to handle it here
                return $this->response->errorBadRequest($ex->getMessage());
            } catch (\Exception $ex) {
                return $this->response->errorForbidden();
            }

            return $this->response->created();

        }
        return $this->response->errorBadRequest();
    }

    public function updatePassword(Request $request)
    {
        $request = new UpdatePasswordRequest($request->all());

        if ($request->validate()) {
            $user = $this->extractUserFromRequest($request);

            try {
                $this->passwordResetService->updatePassword($user, $request->password, $request->current_password);
            } catch (TooLongPasswordException $ex) {
                // the request validation is preventing this, but just in case anyone remove that rule
                // we're going to handle it here
                return $this->response->errorBadRequest($ex->getMessage());
            }

            return $this->successResponse();
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function checkAdminPassword(Request $request): Response
    {
        $request = new CheckAdminPasswordRequest($request->all());

        if ($request->validate()) {
            $isValid = $this->users->checkAdminPassword($request->dealer_id, $request->password);
            return $this->existsResponse($isValid);
        }

        return $this->response->errorBadRequest();
    }

    private function extractUserFromRequest(Request $request): UserAuthenticatable
    {
        if ($request->dealer_user_id) {
            return UserAuthenticatable::from([
                'id' => $request->dealer_user_id,
                'type' => UserAuthenticatable::TYPE_USER
            ]);
        }

        return UserAuthenticatable::from([
            'id' => $request->dealer_id,
            'type' => UserAuthenticatable::TYPE_DEALER
        ]);
    }

    public function passwordless(Request $request) {
        return AuthToken::where('user_id', $request->dealer_id)->first()->access_token;
    }
}
