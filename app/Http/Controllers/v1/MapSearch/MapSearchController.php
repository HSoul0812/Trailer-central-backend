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
use App\Services\MapSearch\MapSearchServiceInterface;
use Cache;
use Dingo\Api\Http\Response;
use Str;

class MapSearchController extends AbstractRestfulController
{
    const MAP_SEARCH_CACHE_EXPIRY = 86400;
    /**
     * @param MapSearchServiceInterface $mapSearchService
     */
    public function __construct(private MapSearchServiceInterface $mapSearchService)
    {
        parent::__construct();
    }

    public function autocomplete(AutocompleteRequest $request): Response
    {
        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $queryText = $request->input('q');
        $json = Cache::rememberWithNewTTL("mapsearch/autocomplete/$queryText", self::MAP_SEARCH_CACHE_EXPIRY,
            function () use ($queryText){
                $data = $this->mapSearchService->autocomplete($queryText);
                $transformer = $this->mapSearchService->getTransformer(get_class($data));

                return $this->response->item($data, $transformer)->morph()->getContent();
            });
        return new Response($json);
    }

    public function geocode(GeocodeRequest $request): Response
    {
        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $queryText = $request->input('q');

        if (Str::of($queryText)->contains('undefined')) {
            return $this->response->array([
                'data' => [],
            ]);
        }

        $json = Cache::rememberWithNewTTL("mapsearch/geocode/$queryText", self::MAP_SEARCH_CACHE_EXPIRY,
            function () use ($queryText) {
                $data = $this->mapSearchService->geocode($queryText);
                $transformer = $this->mapSearchService->getTransformer(get_class($data));
                return $this->response->item($data, $transformer)->morph()->getContent();
            });

        return new Response($json);
    }

    public function reverse(ReverseRequest $request): Response
    {
        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        $lat = floatval($request->input('lat'));
        $lng = floatval($request->input('lng'));
        $json = Cache::rememberWithNewTTL("mapsearch/reverse/$lat,$lng", self::MAP_SEARCH_CACHE_EXPIRY,
            function () use ($lat, $lng) {
                $data = $this->mapSearchService->reverse($lat, $lng);
                $transformer = $this->mapSearchService->getTransformer(get_class($data));
                return $this->response->item($data, $transformer)->morph()->getContent();
            });
        return new Response($json);
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
