<?php

namespace App\Http\Controllers\v1\WebsiteUser;

use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Repositories\WebsiteUser\WebsiteUserRepositoryInterface;
use Illuminate\Http\Request;

class VerificationController extends AbstractRestfulController
{
    public function __construct(private WebsiteUserRepositoryInterface $repository)
    {
        parent::__construct();
    }

    public function verify($userId, $hash, Request $request)
    {
        if (!$request->hasValidSignature()) {
            $this->response->error('Invalid/Expired url provided', 401);
        }

        $user = $this->repository->findOrFail($userId);

        if (sha1($user->getEmailForVerification()) !== $hash) {
            $this->response->error('Invalid email provided', 401);
        }
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        $email = $user->email;
        $verifyUrl = config('auth.verify_url') . "?email=$email";

        return redirect($verifyUrl);
    }

    public function resend()
    {
        if (auth()->user()->hasVerifiedEmail()) {
            $this->response->error('Email already verified', 400);
        }

        auth()->user()->sendEmailVerificationNotification();

        $this->response->error('Email verification link sent on your email id', 200);
    }

    public function index(IndexRequestInterface $request)
    {
        // TODO: Implement index() method.
    }

    public function create(CreateRequestInterface $request)
    {
        // TODO: Implement create() method.
    }

    public function show(int $id)
    {
        // TODO: Implement show() method.
    }

    public function update(int $id, UpdateRequestInterface $request)
    {
        // TODO: Implement update() method.
    }

    public function destroy(int $id)
    {
        // TODO: Implement destroy() method.
    }

    protected function constructRequestBindings(): void
    {
        // TODO: Implement constructRequestBindings() method.
    }
}
