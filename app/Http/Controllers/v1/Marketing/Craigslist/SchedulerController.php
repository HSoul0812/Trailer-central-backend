<?php

namespace App\Http\Controllers\v1\Marketing\Craigslist;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Marketing\Craigslist\UpcomingSchedulerRequest;
use App\Repositories\Marketing\Craigslist\SchedulerRepositoryInterface;
use App\Transformers\Marketing\Craigslist\QueueTransformer;
use Dingo\Api\Http\Request;

class SchedulerController extends RestfulControllerV2
{
    /**
     * @var SchedulerRepositoryInterface
     */
    protected $repository;

    /**
     * @var QueueTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param SchedulerRepositoryInterface $repo
     * @param QueueTransformer $transformer
     */
    public function __construct(
        SchedulerRepositoryInterface $repo,
        QueueTransformer $transformer
    ) {
        $this->repository = $repo;
        $this->transformer = $transformer;

        $this->middleware('setDealerIdOnRequest')->only(['index', 'create', 'upcoming']);
    }

    /**
     * Get Craigslist Scheduler Upcoming Posts
     * 
     * @param Request $request
     * @return type
     */
    public function upcoming(Request $request)
    {
        // Handle Upcoming Scheduler Request
        $request = new UpcomingSchedulerRequest($request->all());
        if ($request->validate()) {
            // Get Upcoming Scheduled Posts
            return $this->response->paginator($this->repository->getUpcoming($request->all()), $this->transformer);
        }
        
        return $this->response->errorBadRequest();
    }
}