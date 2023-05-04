<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Parts;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Parts\Type\IndexTypeRequest;
use App\Http\Requests\UpdateRequestInterface;
use App\Repositories\Parts\TypeRepositoryInterface;
use App\Transformers\Parts\TypeTransformer;
use Dingo\Api\Http\Response;

class TypeController extends AbstractRestfulController
{
    /**
     * Create a new controller instance.
     *
     * @param TypeRepositoryInterface $type
     */
    public function __construct(TypeRepositoryInterface $types, TypeTransformer $typesTransformer)
    {
        $this->typeRepo = $types;
        $this->typesTransformer = $typesTransformer;
        parent::__construct();
    }

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
    public function index(IndexRequestInterface $request): Response
    {
        if ($request->validate()) {
            return $this->response->collection($this->typeRepo->getAll(), $this->typesTransformer);
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

    protected function constructRequestBindings(): void
    {
        app()->bind(IndexRequestInterface::class, function () {
            return inject_request_data(IndexTypeRequest::class);
        });
    }
}
