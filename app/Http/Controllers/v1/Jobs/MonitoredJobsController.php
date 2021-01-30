<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Jobs;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Jobs\GetMonitoredJobsRequest;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Transformers\Jobs\MonitoredJobsTransformer;
use Dingo\Api\Exception\ResourceException;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MonitoredJobsController extends RestfulController
{
    protected $failedMessage = 'This process could not be completed. Please request a new job.';

    /**
     * @var MonitoredJobRepositoryInterface
     */
    private $repository;

    public function __construct(MonitoredJobRepositoryInterface $repository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'status']);

        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *     path="/api/jobs",
     *     description="Retrieve a list of monitored jobs",
     *     tags={"MonitoredJobs"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of monitored jobs",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     )
     * )
     *
     * @param Request $request
     * @throws ResourceException when there were some validation error
     * @throws HttpException when there was a bad request
     * @return Response|void
     *
     */
    public function index(Request $request): Response
    {
        $request = new GetMonitoredJobsRequest($request->all());

        if ($request->validate()) {
            return $this->response->paginator($this->repository->getAll($request->all()), new MonitoredJobsTransformer());
        }

        $this->response->errorBadRequest();
    }

    /**
     * Check status of the process
     *
     * @param string $token The token returned by the create service
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/api/jobs/status/{token}",
     *     description="Check status of the process",
     *     tags={"MonitoredJobs"},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true
     *     )
     * )
     */
    public function status(string $token): JsonResponse
    {
        $job = $this->repository->findByToken($token);

        if ($job === null) {
            $this->response->errorNotFound('The job was not found');
        }

        if ($job->isPending()) {
            return response()->json(['message' => 'Still processing', 'progress' => $job->progress]);
        }

        if ($job->isCompleted()) {
            return response()->json(['message' => 'Completed', 'progress' => $job->progress]);
        }

        if ($job->isFailed()) {
            return response()->json(['message' => $this->failedMessage], 500);
        }

        return response()->json(['message' => 'Error: unknown status'], 500);
    }
}
