<?php

namespace App\Http\Controllers\v1\Marketing\Craigslist;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Marketing\Craigslist\RecentSchedulerRequest;
use App\Http\Requests\Marketing\Craigslist\UpcomingSchedulerRequest;
use App\Repositories\Marketing\Craigslist\ActivePostRepositoryInterface;
use App\Transformers\Marketing\Craigslist\ActivePostTransformer;
use App\Transformers\Marketing\Craigslist\ScheduledPostTransformer;
use Dingo\Api\Http\Request;

class PagetabController extends RestfulControllerV2
{
    /**
     * @var PageRepositoryInterface
     */
    protected $repository;

    /**
     * @var PageTransformer
     */
    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param PageRepositoryInterface $repo
     * @param PageTransformer $transformer
     */
    public function __construct(
        ActivePostRepositoryInterface $activeRepo,
        ActivePostTransformer $activeTransformer,
        ScheduledPostTransformer $scheduledTransformer
    ) {
        $this->activeRepository = $activeRepo;
        $this->activeTransformer = $activeTransformer;
        $this->scheduledTransformer = $scheduledTransformer;

        $this->middleware('setDealerIdOnRequest')->only(['recent', 'upcoming']);
    }

    /**
     * Get Craigslist Scheduler Recent Posts
     * 
     * @param Request $request
     * @return type
     */
    public function recent(Request $request)
    {
        // Handle Recent Scheduler Request
        $request = new RecentSchedulerRequest($request->all());
        if ($request->validate()) {
            // Get Recent Scheduler Posts
            return $this->response->paginator($this->activeRepository->getRecent($request->all()), $this->activeTransformer);
        }
        
        return $this->response->errorBadRequest();
    }

    /**
     * Get Craigslist Scheduler Next Posts
     * 
     * @param Request $request
     * @return type
     */
    public function upcoming(Request $request)
    {
        // Handle Upcoming Scheduler Request
        $request = new UpcomingSchedulerRequest($request->all());
        if ($request->validate()) {
            // Get Upcoming Scheduler Posts
            return $this->response->paginator($this->activeRepository->getUpcoming($request->all()), $this->scheduledTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
}