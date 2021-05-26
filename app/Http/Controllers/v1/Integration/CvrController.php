<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Integration;

use App\Http\Controllers\v1\Jobs\MonitoredJobsController;
use App\Http\Requests\Integration\CVR\SendFileRequest;
use App\Jobs\Integration\CVR\CvrSendFileJob;
use App\Models\Integration\CVR\CvrFile;
use App\Models\Integration\CVR\CvrFilePayload;
use App\Repositories\Common\MonitoredJobRepositoryInterface;
use App\Repositories\Integration\CVR\CvrFileRepositoryInterface;
use App\Services\Integration\CVR\CvrFileServiceInterface;
use App\Transformers\Integration\CVR\CrvFileTransformer;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;
use App\Models\CRM\Dms\UnitSale;


class CvrController extends MonitoredJobsController
{
    /**
     * @var CvrFileRepositoryInterface
     */
    protected $repository;

    /**
     * @var CvrFileServiceInterface
     */
    protected $service;

    public function __construct(
        CvrFileRepositoryInterface $repository,
        MonitoredJobRepositoryInterface $jobsRepository,
        CvrFileServiceInterface $service)
    {
        parent::__construct($jobsRepository);

        $this->middleware('setDealerIdOnRequest')->only(['create']);

        $this->repository = $repository;
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *     path="/api/integration/cvr",
     *     description="Enqueue a job to send a zipped file to the dealer CVR account",
     *     tags={"CVR"},
     *     operationId="create",
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="path",
     *         description="The dealer ID.",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         description="The token for the job.",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Enqueue a CVR file to be sent",
     *         content={
     *              @OA\MediaType(
     *                  mediaType="application/json",
     *                  @OA\Schema(
     *                      type="object",
     *                      @OA\Property(
     *                          property="data",
     *                          description="data wrapper",
     *                          type="object",
     *                          @OA\Property(
     *                              property="id",
     *                              description="uuid of the queued job",
     *                              type="string"
     *                          ),
     *                          @OA\Property(
     *                              property="status",
     *                              description="status of the queued job",
     *                              type="string",
     *                              enum={"pending", "processing", "completed", "failed"},
     *                          ),
     *                          @OA\Property(
     *                              property="errors",
     *                              description="job time sending errors",
     *                              type="array",
     *                              @OA\Items(
     *                                  type="array",
     *                                  @OA\Items(type="string")
     *                              ),
     *                          ),
     *                      ),
     *                      example={
     *                          "data": {
     *                              "id": "237b164c-b0ff-4ba2-82f9-682647599f5c",
     *                              "status": "pending",
     *                              "errors": {}
     *                          }
     *                      }
     *                  )
     *              )
     *         }
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Failed",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="array",
     *                         description="The response errors collection",
     *                         @OA\Items(
     *                              type="array",
     *                              @OA\Items(type="string")
     *                          ),
     *                     ),
     *                     @OA\Property(
     *                         property="status_code",
     *                         type="integer",
     *                         description="The response status code number acording to the rfc4918"
     *                     ),
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="The response message"
     *                     ),
     *                     example={
     *                          "message": "Validation Failed",
     *                          "errors": {
     *                              "document": "The dealer_id field is required."
     *                          },
     *                          "status_code": 422
     *                      }
     *                 )
     *             )
     *         }
     *     )
     * )
     *
     * @param Request $request
     * @return Response|void
     * @throws BusyJobException
     * @throws HttpException when some validation rule has not passed
     */
    public function create(Request $request): Response
    {
        $request = new SendFileRequest($request->all());

        if ($request->validate()) {
            $model = $this->service
                ->setup($request->get('dealer_id'), CvrFilePayload::from(['unit_sale_id' => $request->get('unit_sale_id')]), $request->get('token'))
                ->withQueueableJob(static function (CvrFile $job): CvrSendFileJob {
                    return new CvrSendFileJob($job->token);
                });

            $this->service->dispatch($model);

            return $this->response->item($model, new CrvFileTransformer);
        }

        $this->response->errorBadRequest();
    }

    /**
     *
     * @OA\Get(
     *     path="/api/integration/cvr/{token}",
     *     description="Check status of the job process",
     *     tags={"CVR"},
     *     @OA\Parameter(
     *         name="token",
     *         in="path",
     *         required=true
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status of the desired job",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="message",
     *                         type="string",
     *                         description="The response message status"
     *                     ),
     *                     @OA\Property(
     *                         property="progress",
     *                         type="float",
     *                         description="Progress of the enqueue job 0-100"
     *                     ),
     *                     example={
     *                           "message": "It is pending",
     *                           "progress": 0
     *                     }
     *                 )
     *             )
     *         }
     *     )
     * )
     */
}
