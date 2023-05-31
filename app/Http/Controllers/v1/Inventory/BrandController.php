<?php

namespace App\Http\Controllers\v1\Inventory;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\Inventory\Brand\IndexBrandRequest;
use App\Http\Requests\UpdateRequestInterface;
use App\Transformers\Inventory\BrandTransformer;
use Dingo\Api\Http\Response;
use Illuminate\Support\Facades\Http;

class BrandController extends AbstractRestfulController
{
    protected $typesTransformer;
    /**
     * Create a new controller instance.
     *
     */
    public function __construct(BrandTransformer $typesTransformer)
    {
        $this->typesTransformer = $typesTransformer;
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function index(IndexRequestInterface $request): Response
    {
        if ($request->validate()) {
            $brands = Http::tcApi()->get('inventory/brands')
                ->throw()
                ->json();
            return $this->response->collection($brands, $this->typesTransformer);
        }

        return $this->response->errorBadRequest();
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
            return inject_request_data(IndexBrandRequest::class);
        });
    }
}
