<?php

namespace App\Http\Controllers\v1\Marketing\Craigslist;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\Marketing\Craigslist\ProfileRepositoryInterface;
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
     * @param ProfileTransformer $transformer
     * @param ProfileAccountTransformer $accountTransformer
     */
    public function __construct(
        ProfileRepositoryInterface $repo,
        ProfileTransformer $transformer,
        ProfileAccountTransformer $accountTransformer
    ) {
        $this->repository = $repo;
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
            $paginator = $this->repository->getPaginator();
            return $this->collectionResponse($this->service->profiles($request->all()),
                                                $this->accountTransformer, $paginator);
        }
        
        return $this->response->errorBadRequest();
    }
}