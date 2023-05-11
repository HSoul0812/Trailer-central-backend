<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Lead;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Lead\CreateLeadRequest;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\Leads\LeadServiceInterface;
use App\Transformers\Lead\TcApiResponseLeadTransformer;
use Dingo\Api\Http\Response;

class LeadController extends AbstractRestfulController
{
    /**
     * Create a new controller instance.
     *
     * @param LeadServiceInterface $lead
     */
    public function __construct(private LeadServiceInterface $leadService, private TcApiResponseLeadTransformer $transformer)
    {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function create(CreateRequestInterface $request)
    {
        if ($request->validate()) {
            return $this->response->item($this->leadService->create($request->all()), $this->transformer);
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
    public function show(int $id): Response
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
            return inject_request_data(CreateLeadRequest::class);
        });
    }
}
