<?php

namespace App\Http\Controllers\v1\SysConfig;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\SysConfig\IndexSysConfigRequest;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\SysConfig\SysConfigServiceInterface;
use Dingo\Api\Http\Response;

class SysConfigController extends AbstractRestfulController
{
    public function __construct(private SysConfigServiceInterface $service)
    {
        parent::__construct();
    }

    public function index(IndexRequestInterface $request): Response
    {
        $config = $this->service->list();

        return $this->response->array($config);
    }

    public function create(CreateRequestInterface $request)
    {
        return new NotImplementedException();
    }

    public function show(int $id)
    {
        return new NotImplementedException();
    }

    public function update(int $id, UpdateRequestInterface $request)
    {
        return new NotImplementedException();
    }

    public function destroy(int $id)
    {
        return new NotImplementedException();
    }

    protected function constructRequestBindings(): void
    {
        app()->bind(IndexRequestInterface::class, function () {
            return inject_request_data(IndexSysConfigRequest::class);
        });
    }
}
