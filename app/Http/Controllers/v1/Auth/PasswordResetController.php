<?php

namespace App\Http\Controllers\v1\Auth;

use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\UpdateRequestInterface;
use App\Http\Requests\WebsiteUser\PasswordResetRequest;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Glossary\IndexGlossaryRequest;
use App\Http\Requests\IndexRequestInterface;
use Dingo\Api\Http\Request;

class PasswordResetController extends AbstractRestfulController
{
    //
    public function postEmail(CreateRequestInterface $request) {
        if($request->validate()) {

        }
        return $this->response->errorBadRequest();
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
        app()->bind(CreateRequestInterface::class, function () {
            return inject_request_data(PasswordResetRequest::class);
        });
    }
}
