<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Jobs;

use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Exceptions\NotImplementedException;
use App\Http\Controllers\RestfulController;
use App\Http\Requests\Jobs\GetMonitoredJobsRequest;
use App\Http\Requests\Jobs\ReadMonitoredJobsRequest;
use App\Models\Common\MonitoredJob;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Transformers\Jobs\MonitoredJobsTransformer;
use Dingo\Api\Exception\ResourceException;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use Illuminate\Http\JsonResponse;

class MonitoredJobsController extends RestfulController
{
    protected $failedMessage = 'This process could not be completed. Please request a new job.';

    /**
     * @var MonitoredJobRepositoryInterface
     */
    private $repository;

    public function __construct(MonitoredJobRepositoryInterface $repository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index', 'status', 'statusByToken']);

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
     * @return Response|void
     *
     * @throws HttpException when there was a bad request
     * @throws ResourceException when there were some validation error
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
     * @param Request $request
     * @return JsonResponse|void
     * @throws HttpException when there was a bad request
     *
     * @OA\Get(
     *     path="/api/jobs/status/{token}",
     *     description="Check status of the process",
     *     tags={"MonitoredJobs"},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function statusByToken(string $token, Request $request): ?JsonResponse
    {
        $request = new GetMonitoredJobsRequest(array_merge($request->all(), ['token' => $token]));

        if ($request->validate()) {
            $job = $request->getJob();

            if ($job === null) {
                $this->response->errorNotFound('Job not found');
            }

            if ($job->isPending()) {
                return response()->json(['message' => 'It is pending', 'progress' => $job->progress]);
            }

            if ($job->isProcessing()) {
                return response()->json(['message' => 'Still processing', 'progress' => $job->progress]);
            }

            if ($job->isCompleted()) {
                return response()->json(['message' => 'Completed', 'progress' => $job->progress]);
            }

            return response()->json(['message' => $this->failedMessage], 500);
        }

        $this->response->errorBadRequest();
    }

    /**
     * Check status of the process
     *
     * @param Request $request
     * @return JsonResponse|void
     * @throws HttpException when there was a bad request
     *
     * @OA\Get(
     *     path="/api/jobs/status",
     *     description="Check status of the process",
     *     tags={"MonitoredJobs"},
     *     @OA\Parameter(
     *         name="token",
     *         in="query",
     *         description="The job token",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function status(Request $request): ?JsonResponse
    {
        $request = new GetMonitoredJobsRequest($request->all());

        if ($request->validate()) {
            return $this->statusByToken($request->get('token'), $request);
        }

        $this->response->errorBadRequest();
    }

    /**
     * Download the completed file created from the request
     *
     * @param string $token
     * @param Request $request
     * @return JsonResponse|StreamedResponse|void
     */
    public function readByToken(string $token, Request $request)
    {
        $request = new ReadMonitoredJobsRequest(array_merge($request->all(), ['token' => $token]));

        if ($request->validate()) {
            $job = $this->repository->findByToken($request->get('token'));

            if ($job === null) {
                $this->response->errorNotFound('Job not found');
            }

            if ($job->isPending()) {
                return response()->json(['message' => 'It is pending', 'progress' => $job->progress], 202);
            }

            if ($job->isProcessing()) {
                return response()->json(['message' => 'Still processing', 'progress' => $job->progress]);
            }

            if ($job->isFailed()) {
                return response()->json([
                    'message' => 'This file could not be completed. Please request a new file.',
                ], 500);
            }

            return $this->readStream($job);
        }

        $this->response->errorBadRequest();
    }

    /**
     * Download the completed file created from the request
     *
     * @param Request $request
     * @return JsonResponse|StreamedResponse|void
     */
    public function read(Request $request)
    {
        $request = new ReadMonitoredJobsRequest($request->all());

        if ($request->validate()) {
            return $this->readByToken($request->get('token'), $request);
        }

        $this->response->errorBadRequest();
    }

    /**
     * @param MonitoredJob $job
     * @return StreamedResponse
     * @throws NotImplementedException
     */
    protected function readStream($job): StreamedResponse
    {
        throw new NotImplementedException('Not implemented yet');
    }
}
