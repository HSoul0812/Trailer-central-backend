<?php

namespace App\Http\Controllers\v1\Home;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Home\IndexHomeRequest;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;

class HomeController extends AbstractRestfulController
{
    /**
     * {@inheritDoc}
     */
    public function create(CreateRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     */
    public function destroy(int $id)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     */
    public function index(IndexRequestInterface $request)
    {
        if ($request->validate()) {
            return $this->response->noContent();
        }

        return $this->response->errorBadRequest();
    }

    /**
     * {@inheritDoc}
     */
    public function show(int $id)
    {
        throw new NotImplementedException();
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, UpdateRequestInterface $request)
    {
        throw new NotImplementedException();
    }

    protected function constructRequestBindings()
    {
        app()->bind(IndexRequestInterface::class, function () {
            return inject_request_data(IndexHomeRequest::class);
        });
    }
}
