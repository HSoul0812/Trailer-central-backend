<?php

namespace App\Http\Controllers\v1\Website\Parts\Textrail;

use App\Http\Controllers\RestfulController;
use Dingo\Api\Http\Request;
use App\Repositories\Website\Parts\FilterRepositoryInterface;
use App\Http\Requests\Website\Parts\Textrail\GetFiltersRequest;
use App\Transformers\Website\Parts\Textrail\FilterTransformer;
use App\Models\Traits\Parts\Cache;

class FilterController extends RestfulController
{
    use Cache;

    protected $filters;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(FilterRepositoryInterface $filters)
    {
        $this->filters = $filters;
    }

     /**
     * @OA\Get(
     *     path="/api/website/parts/textrail/filters",
     *     description="Retrieve a list of filters",
     *     tags={"Website Part Filters"},
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of parts",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $request = new GetFiltersRequest($request->all());

        if ($request->validate()) {
            return $this->response->collection($this->filters->getAll($request->all()), new FilterTransformer);
        }

        return $this->response->errorBadRequest();
    }

    protected function getCacheName() {
        return 'parts_filter_cache';
    }

}
