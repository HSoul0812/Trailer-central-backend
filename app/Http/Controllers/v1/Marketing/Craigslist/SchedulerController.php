<?php

namespace App\Http\Controllers\v1\Marketing\Craigslist;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Marketing\Craigslist\GetSchedulerRequest;
use App\Http\Requests\Marketing\Craigslist\UpcomingSchedulerRequest;
use App\Repositories\Marketing\Craigslist\SchedulerRepositoryInterface;
use App\Transformers\Marketing\Craigslist\QueueTransformer;
use App\Transformers\Marketing\Craigslist\ScheduleTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

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
     * @var ScheduleTransformer
     */
    protected $scheduleTransformer;

    /**
     * Create a new controller instance.
     *
     * @param SchedulerRepositoryInterface $repo
     * @param QueueTransformer $transformer
     * @param ScheduleTransformer $scheduleTransformer
     */
    public function __construct(
        SchedulerRepositoryInterface $repo,
        QueueTransformer $transformer,
        ScheduleTransformer $scheduleTransformer
    ) {
        $this->repository = $repo;
        $this->transformer = $transformer;
        $this->scheduleTransformer = $scheduleTransformer;

        $this->middleware('setDealerIdOnRequest')->only(['index', 'create', 'upcoming']);
    }

    /**
     * Get Scheduler Calendar Range
     *
     * @param Request $request
     * @return Response
     *
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
     */
    public function index(Request $request)
    {
        $request = new GetSchedulerRequest($request->all());

        if ($request->validate()) {
            return $this->response->collection($this->repository->scheduler($request->all()), $this->scheduleTransformer);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Get Craigslist Scheduler Upcoming Posts
     *
     * @param Request $request
     * @return Response|null
     *
     * @throws NoObjectIdValueSetException
     * @throws NoObjectTypeSetException
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
