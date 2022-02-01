<?php

namespace App\Http\Controllers\v1\Marketing\Craigslist;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Marketing\Craigslist\GetActivePostsRequest;
use App\Repositories\Marketing\Craigslist\ActivePostRepositoryInterface;
use App\Transformers\Marketing\Craigslist\ActivePostTransformer;
use Dingo\Api\Http\Request;

class ActivePostController extends RestfulControllerV2
{
    /**
     * @var ActivePostRepositoryInterface
     */
    protected $repository;

    /**
     * @var ActivePostTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param ActivePostRepositoryInterface $repo
     * @param ActivePostTransformer $transformer
     */
    public function __construct(
        ActivePostRepositoryInterface $repo,
        ActivePostTransformer $transformer
    ) {
        $this->repository = $repo;
        $this->transformer = $transformer;
    }

    /**
     * Get Craigslist Active Posts
     * 
     * @param Request $request
     * @return type
     */
    public function index(Request $request)
    {
        // Handle Get Active Posts Request
        $request = new GetActivePostsRequest($request->all());
        if ($request->validate()) {
            // Get Active Posts
            return $this->response->paginator($this->repository->getAll($request->all()), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }
}