<?php

namespace App\Http\Controllers\v1\IpInfo;

use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\IpInfo\IpInfoRequest;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\IpInfo\IpInfoServiceInterface;
use App\Transformers\IpInfo\CityTransformer;
use Illuminate\Http\Request;

class IpInfoController extends AbstractRestfulController
{
    public function __construct(
        private IpInfoServiceInterface $service,
        private CityTransformer $transformer
    )
    {
        parent::__construct();
    }


    public function index(IndexRequestInterface $request)
    {
        if($request->validate()) {
            $ip = $request->get(
                'ip',
                $this->service->getRemoteIPAddress() ?? request()->ip()
            );
            if(!$ip) {
                $this->response->errorBadRequest('No IP was detected');
            }
            return $this->response->item($this->service->city($ip), $this->transformer);
        }
        return $this->response->errorBadRequest();
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
        app()->bind(IndexRequestInterface::class, function() {
            return inject_request_data(IpInfoRequest::class);
        });
    }
}
