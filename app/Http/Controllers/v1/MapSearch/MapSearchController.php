<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\MapSearch;

use App\Exceptions\NotImplementedException;
use App\Http\Controllers\AbstractRestfulController;
use App\Http\Requests\CreateRequestInterface;
use App\Http\Requests\IndexRequestInterface;
use App\Http\Requests\MapService\AutocompleteRequest;
use App\Http\Requests\MapService\GeocodeRequest;
use App\Http\Requests\MapService\ReverseRequest;
use App\Http\Requests\UpdateRequestInterface;
use App\Services\MapSearchService\MapSearchServiceInterface;

class MapSearchController extends AbstractRestfulController
{
    private MapSearchServiceInterface $mapSearchService;

    public function __construct(MapSearchServiceInterface $mapSearchService)
    {
        parent::__construct();
        $this->mapSearchService = $mapSearchService;
    }

    public function autocomplete(AutocompleteRequest $request)
    {
        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $data = $this->mapSearchService->autocomplete($request->input('q'));
        $transformer = $this->mapSearchService->getTransformer();

        return $this->response->item($data, $transformer);
    }

    public function geocode(GeocodeRequest $request)
    {
        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $data = $this->mapSearchService->geocode($request->input('q'));
        $transformer = $this->mapSearchService->getTransformer();

        return $this->response->item($data, $transformer);
    }

    public function reverse(ReverseRequest $request)
    {
        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $lat = floatval($request->input('lat'));
        $lng = floatval($request->input('lng'));

        $data = $this->mapSearchService->reverse($lat, $lng);
        $transformer = $this->mapSearchService->getTransformer();

        return $this->response->item($data, $transformer);
    }

    public function index(IndexRequestInterface $request)
    {
        return new NotImplementedException();
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
        app()->bind(AutocompleteRequest::class, function () {
            return inject_request_data(AutocompleteRequest::class);
        });
        app()->bind(GeocodeRequest::class, function () {
            return inject_request_data(GeocodeRequest::class);
        });
        app()->bind(ReverseRequest::class, function () {
            return inject_request_data(ReverseRequest::class);
        });
    }
}
