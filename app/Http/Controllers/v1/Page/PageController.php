<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Page;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Page\IndexPageRequest;
use App\Http\Requests\UpdateRequestInterface;
use App\Repositories\Page\PageRepositoryInterface;
use App\Transformers\Page\PageTransformer;
use Dingo\Api\Http\Response;

class PageController extends AbstractRestfulController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private PageRepositoryInterface $pageRepo,
        private PageTransformer $pageTransformer
    ) {
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
            return $this->response->collection($this->pageRepo->getAll(), $this->pageTransformer);
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
            return inject_request_data(IndexPageRequest::class);
        });
    }
}
