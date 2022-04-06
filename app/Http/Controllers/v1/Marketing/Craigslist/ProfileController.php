<?php

namespace App\Http\Controllers\v1\Marketing\Craigslist;

use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\Marketing\Craigslist\ProfileRepositoryInterface;
use App\Transformers\Marketing\Craigslist\ProfileTransformer;
use Dingo\Api\Http\Request;
use App\Http\Requests\Marketing\Craigslist\GetProfileRequest;

class ProfileController extends RestfulControllerV2
{
    /**
     * @var ProfileRepositoryInterface
     */
    protected $repository;

    /**
     * Create a new controller instance.
     *
     * @param ProfileRepositoryInterface $repo
     */
    public function __construct(
        ProfileRepositoryInterface $repo
    ) {
        $this->repository = $repo;
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
            return $this->collectionResponse($this->repository->getAll($request->all()), new ProfileTransformer(), $paginator);
        }
        
        return $this->response->errorBadRequest();
    }
}