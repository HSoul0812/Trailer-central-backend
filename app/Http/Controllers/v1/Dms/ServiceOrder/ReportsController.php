<?php

declare(strict_types=1);

namespace App\Http\Controllers\v1\Dms\ServiceOrder;

use App\Http\Controllers\RestfulControllerV2 as RestfulController;
use App\Http\Requests\Dms\ServiceOrder\GetMonthlyReportRequest;
use App\Repositories\Dms\ServiceOrder\ServiceReportRepositoryInterface;
use App\Transformers\Dms\ServiceOrder\MonthlyHoursReportTransformer;
use Dingo\Api\Exception\ResourceException;
use Dingo\Api\Http\Response;
use Dingo\Api\Http\Request;
use Exception;

class ReportsController extends RestfulController
{
    /** @var ServiceReportRepositoryInterface */
    private $repository;

    public function __construct(ServiceReportRepositoryInterface $repository)
    {
        $this->middleware('setDealerIdOnRequest')->only(['monthly']);

        $this->repository = $repository;
    }

    /**
     * @OA\Get(
     *     path="/api/dms/reports/service-monthly-hours",
     *     description="Retrieve a report of services hours by dealer",
     *     tags={"Inventory"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort order can be: in:month_name,-month_name,type,-type,unit_price,-unit_price",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         in="query",
     *         description="Search String",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="delaer identifier",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a report of services hours by dealer",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Some validation error.",
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Error: Bad request.",
     *     ),
     * )
     *
     * @param Request $request
     * @return Response
     *
     * @throws ResourceException when there were some validation error
     * @throws Exception when there were db errors
     */
    public function monthly(Request $request): Response
    {
        $request = new GetMonthlyReportRequest($request->all());

        if (!$request->validate()) {
            $this->response->errorBadRequest();
        }

        return $this->response->paginator($this->repository->monthly($request->all()), new MonthlyHoursReportTransformer());
    }
}
