<?php

namespace App\Http\Controllers\v1\Marketing\Craigslist;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\Marketing\Craigslist\ProfileRepositoryInterface;
use App\Services\Marketing\Craigslist\ProfileServiceInterface;
use App\Transformers\Marketing\Craigslist\ProfileTransformer;
use App\Transformers\Marketing\Craigslist\ProfileAccountTransformer;
use Dingo\Api\Http\Request;
use App\Http\Requests\Marketing\Craigslist\GetProfileRequest;

class ProfileController extends RestfulControllerV2
{
    /**
     * @var ProfileRepositoryInterface
     */
    protected $repository;

    /**
     * @var ProfileServiceInterface
     */
    protected $service;

    /**
     * @var ProfileTransformer
     */
    protected $transformer;

    /**
     * @var ProfileAccountTransformer
     */
    protected $accountTransformer;

    /**
     * Create a new controller instance.
     *
     * @param ProfileRepositoryInterface $repo
     * @param ProfileServiceInterface $service
     * @param ProfileTransformer $transformer
     * @param ProfileAccountTransformer $accountTransformer
     */
    public function __construct(
        ProfileRepositoryInterface $repo,
        ProfileServiceInterface $service,
        ProfileTransformer $transformer,
        ProfileAccountTransformer $accountTransformer
    ) {
        $this->repository = $repo;
        $this->service = $service;
        $this->transformer = $transformer;
        $this->accountTransformer = $accountTransformer;

        $this->middleware('setDealerIdOnRequest')->only(['index']);
    }

    /**
     * Get Profiles
     * 
     * @param Request $request
     * @return type
     */
    public function index(Request $request)
    {
        // Handle Profile Request
        $request = new GetProfileRequest($request->all());
        if ($request->validate()) {
            // Get Profiles
            return $this->item($this->service->profiles($request->all()), $this->accountTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
}