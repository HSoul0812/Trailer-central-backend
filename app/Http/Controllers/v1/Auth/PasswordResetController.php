<?php

namespace App\Http\Controllers\v1\Auth;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\UpdateRequestInterface;
use App\Http\Requests\WebsiteUser\ForgetPasswordRequest;
use App\Http\Requests\WebsiteUser\ForgetPasswordRequestInterface;
use App\Http\Requests\WebsiteUser\PasswordResetRequest;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\WebsiteUser\PasswordResetRequestInterface;

class PasswordResetController extends AbstractRestfulController
{
    //
    public function index(IndexRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    public function create(CreateRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    public function show(int $id)
    {
        throw new NotImplementedException();
    }

    public function update(int $id, UpdateRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    public function destroy(int $id)
    {
        throw new NotImplementedException();
    }

    public function forgetPassword(ForgetPasswordRequestInterface $request) {
        if($request->validate()) {

        }

        $this->response->errorBadRequest();
    }

    public function resetPassword(PasswordResetRequestInterface $request) {
        if($request->validate()) {

        }

        $this->response->errorBadRequest();
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(ForgetPasswordRequestInterface::class, function () {
            return inject_request_data(ForgetPasswordRequest::class);
        });

        app()->bind(PasswordResetRequestInterface::class, function () {
           return inject_request_data(PasswordResetRequest::class);
        });
    }
}
