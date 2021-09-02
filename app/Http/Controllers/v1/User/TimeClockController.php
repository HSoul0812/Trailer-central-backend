<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\User;

use App\Exceptions\Requests\Validation\NoObjectIdValueSetException;
use App\Exceptions\Requests\Validation\NoObjectTypeSetException;
use App\Http\Requests\CRM\User\GetTimeClockEmployeesRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Requests\CRM\User\GetTimeClockRequest;
use App\Services\CRM\User\TimeClockServiceInterface;
use App\Transformers\CRM\User\TimeClockTransformer;
use App\Http\Requests\CRM\User\PostTimeClockPunchRequest;
use App\Repositories\CRM\User\EmployeeRepositoryInterface;
use App\Transformers\CRM\User\EmployeeTransformer;
use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use Dingo\Api\Http\Response;

class TimeClockController extends RestfulControllerV2
{
    /** @var TimeClockServiceInterface */
    private $service;

    /** @var EmployeeRepositoryInterface */
    private $employeeRepository;

    public function __construct(
        TimeClockServiceInterface $timeClockService,
        EmployeeRepositoryInterface $employeeRepository
    ) {
        $this->middleware('setDealerIdOnRequest');

        $this->service = $timeClockService;
        $this->employeeRepository = $employeeRepository;
    }

    /**
     * Starts/stop the clock for given employee.
     *
     * @return void|Response
     *
     * @throws HttpException when some validation has failed
     * @throws NoObjectIdValueSetException when validateObjectBelongsToUser is set to true but getObjectIdValue is set to false
     * @throws NoObjectTypeSetException when validateObjectBelongsToUser is set to true but getObject is set to false
     */
    public function punch(Request $baseRequest)
    {
        $request = new PostTimeClockPunchRequest($baseRequest->all());

        if ($request->validate()) {
            return $this->response->item(
                $this->service->punch($request->getEmployeeId()),
                new TimeClockTransformer()
            );
        }

        $this->response->errorBadRequest();
    }

    /**
     * Starts the clock for given employee.
     *
     * @return void|Response
     *
     * @throws HttpException when some validation has failed
     * @throws NoObjectIdValueSetException when validateObjectBelongsToUser is set to true but getObjectIdValue is set to false
     * @throws NoObjectTypeSetException when validateObjectBelongsToUser is set to true but getObject is set to false
     */
    public function tracking(Request $baseRequest)
    {
        $request = new GetTimeClockRequest($baseRequest->all());

        if ($request->validate()) {
            $tracking = $this->service->trackingByEmployee(
                $request->getEmployeeId(),
                $request->getFromDate(),
                $request->getToDate()
            );

            return $this->response->array([
                'data' => [
                    'timelog' => collect($tracking->timelog)->map(function ($x, $y) {
                        return (new TimeClockTransformer())->transform($x);
                    }),
                    //'worklog' => $tracking->worklog
                ],
                'meta' => $tracking->summary->asArray()
            ]);
        }

        $this->response->errorBadRequest();
    }

    /**
     * Gets the employee list depending on user permissions
     *
     * @param  Request  $baseRequest
     *
     * @return void|Response
     *
     * @throws HttpException when some validation has failed
     * @throws NoObjectIdValueSetException when validateObjectBelongsToUser is set to true but getObjectIdValue is set to false
     * @throws NoObjectTypeSetException when validateObjectBelongsToUser is set to true but getObject is set to false
     */
    public function employees(Request $baseRequest)
    {
        $request = new GetTimeClockEmployeesRequest($baseRequest->all());

        if ($request->validate()) {
            return $this->response->paginator(
                $this->employeeRepository->findWhoHasTimeClockEnabled($request->all()),
                new EmployeeTransformer()
            );
        }

        $this->response->errorBadRequest();
    }
}
