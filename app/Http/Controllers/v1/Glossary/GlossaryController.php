<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Glossary;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\Glossary\IndexGlossaryRequest;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\UpdateRequestInterface;
use App\Repositories\Glossary\GlossaryRepositoryInterface;
use App\Transformers\Glossary\GlossaryTransformer;
use Dingo\Api\Http\Response;

class GlossaryController extends AbstractRestfulController
{
    /**
     * Create a new controller instance.
     */
    public function __construct(GlossaryRepositoryInterface $glossary, GlossaryTransformer $glossaryTransformer)
    {
        $this->glossaryRepo = $glossary;
        $this->glossaryTransformer = $glossaryTransformer;
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
            return $this->response->collection($this->glossaryRepo->getAll(), $this->glossaryTransformer);
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
            return inject_request_data(IndexGlossaryRequest::class);
        });
    }
}
