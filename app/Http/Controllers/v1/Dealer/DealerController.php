<?php

namespace App\Http\Controllers\v1\Dealer;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Dealer\IndexDealerRequest;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\Dealers\DealerServiceInterface;
use App\Transformers\Dealer\TcApiResponseDealerTransformer;

class DealerController extends AbstractRestfulController
{
    public function __construct(
        private DealerServiceInterface $dealerService,
    ) {
        parent::__construct();
    }

    public function index(IndexRequestInterface $request)
    {
        $request->validate();

        return $this->response->collection(
            $this->dealerService->dealersList($request->all()),
            new TcApiResponseDealerTransformer()
        );
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

    protected function constructRequestBindings(): void
    {
        app()->bind(IndexRequestInterface::class, function () {
            return inject_request_data(IndexDealerRequest::class);
        });
    }
}
