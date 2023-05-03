<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\SubscribeEmailSearch;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\SubscribeEmailSearch\CreateSubscribeEmailSearchRequest;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\SubscribeEmailSearch\SubscribeEmailSearchServiceInterface;
use App\Transformers\SubscribeEmailSearch\SubscribeEmailSearchTransformer;
use Dingo\Api\Http\Response;

class SubscribeEmailSearchController extends AbstractRestfulController
{
    /**
     * Create a new controller instance.
     *
     * @param SubscribeEmailSearchServiceInterface $subscribeEmailSearch
     */
    public function __construct(private SubscribeEmailSearchServiceInterface $subscribeEmailSearchService, private SubscribeEmailSearchTransformer $transformer)
    {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function create(CreateRequestInterface $request)
    {
        if ($request->validate()) {
            return $this->response->item($this->subscribeEmailSearchService->send($request->all()), $this->transformer);
        }

        return $this->response->errorBadRequest();
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
        throw new NotImplementedException();
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
        app()->bind(CreateRequestInterface::class, function () {
            return inject_request_data(CreateSubscribeEmailSearchRequest::class);
        });
    }
}
