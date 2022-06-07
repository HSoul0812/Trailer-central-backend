<?php

namespace App\Http\Controllers\v1\IpInfo;

use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use Illuminate\Http\Request;

class IpInfoController extends AbstractRestfulController
{
    //
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
