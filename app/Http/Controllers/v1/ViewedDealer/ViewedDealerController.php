<?php

namespace App\Http\Controllers\v1\ViewedDealer;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Http\Requests\ViewedDealer\CreateViewedDealerRequest;
use App\Http\Requests\ViewedDealer\IndexViewedDealerRequest;
use App\Repositories\ViewedDealer\ViewedDealerRepositoryInterface;
use App\Transformers\ViewedDealer\ViewedDealerIndexTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class ViewedDealerController extends AbstractRestfulController
{
    public function __construct(
        private ViewedDealerRepositoryInterface $repository,
    )
    {
        parent::__construct();
    }

    public function index(IndexRequestInterface $request)
    {
        $request->validate();

        $name = $request->input('name');

        try {
            return $this->response->item(
                item: $this->repository->findByName($name),
                transformer: resolve(ViewedDealerIndexTransformer::class),
            );
        } catch (ModelNotFoundException) {
            $this->response->errorNotFound("Not found dealer id from name '$name'.");
        }
    }

    public function create(CreateRequestInterface $request)
    {
        $request->validate();

        try {
            return $this->response->array([
                'data' => $this->repository->create($request->input('viewed_dealers')),
            ]);
        } catch (Throwable $e) {
            $statusCode = $e->getCode();

            if (gettype($statusCode) === 'string') {
                $statusCode = 422;
            }

            $this->response->error(
                message: $e->getMessage(),
                statusCode: $statusCode,
            );
        }
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
            return inject_request_data(IndexViewedDealerRequest::class);
        });

        app()->bind(CreateRequestInterface::class, function () {
            return inject_request_data(CreateViewedDealerRequest::class);
        });
    }
}
