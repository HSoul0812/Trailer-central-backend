<?php

namespace App\Http\Controllers\v1\User;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\CRM\User\GetTimeClockStatusRequest;
use App\Http\Requests\CRM\User\PostTimeClockPunchInRequest;
use App\Http\Requests\CRM\User\PostTimeClockPunchOutRequest;
use App\Repositories\CRM\User\TimeClockRepositoryInterface;
use Dingo\Api\Http\Request;

class TimeClockController extends RestfulControllerV2
{
    private $timeClockRepository;

    public function __construct(TimeClockRepositoryInterface $timeClockRepository)
    {
        $this->middleware('setDealerIdOnRequest');
        $this->timeClockRepository = $timeClockRepository;
    }

    /**
     * Checks if the clock for given user is ticking.
     */
    public function status(Request $request)
    {
        $request = new GetTimeClockStatusRequest($request->all());
        if ($request->validate()) {
            return $this->response->array([
                'status' => $this->timeClockRepository->isClockTicking($request->user_id),
            ]);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Starts the clock for given user/employee.
     */
    public function punchIn(Request $request)
    {
        $request = new PostTimeClockPunchInRequest($request->all());
        if ($request->validate()) {
            return $this->response->array([
                'status' => $this->timeClockRepository->markPunchIn($request->user_id),
            ]);
        }

        return $this->response->errorBadRequest();
    }

    /**
     * Stops the clock for given user/employee.
     */
    public function punchOut(Request $request)
    {
        $request = new PostTimeClockPunchOutRequest($request->all());
        if ($request->validate()) {
            return $this->response->array([
                'status' => $this->timeClockRepository->markPunchOut($request->user_id),
            ]);
        }

        return $this->response->errorBadRequest();
    }
}
