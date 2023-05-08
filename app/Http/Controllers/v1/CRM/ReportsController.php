<?php

namespace App\Http\Controllers\v1\CRM;

use App\Http\Controllers\RestfulControllerV2;
use Dingo\Api\Http\Request;
use App\Repositories\CRM\Report\ReportRepositoryInterface;
use App\Repositories\CRM\Report\ReportRepository;
use App\Http\Requests\CRM\Report\GetReportRequest;
use App\Http\Requests\CRM\Report\CreateReportRequest;
use App\Http\Requests\CRM\Report\DeleteReportRequest;
use App\Http\Requests\CRM\Report\GetFilteredLeadsReportRequest;
use App\Http\Requests\CRM\Report\GetFilteredInventoriesReportRequest;

class ReportsController extends RestfulControllerV2
{
    /**
     * @var ReportRepositoryInterface
     */
    protected $repository;

    public function __construct(ReportRepository $repository)
    {
        $this->repository = $repository;

        $this->middleware('setUserIdOnRequest')->only(['index', 'create', 'destroy']);
        $this->middleware('setDealerIdOnRequest')->only(['getFilteredLeads', 'getFilteredInventories']);
    }

    public function index(Request $request)
    {
        $request = new GetReportRequest($request->all());

        if ($request->validate()) {

            return $this->response->array(['data' => $this->repository->getAll($request->all())]);
        }

        return $this->response->errorBadRequest();
    }

    public function create(Request $request)
    {
        $request = new CreateReportRequest($request->all());

        if ($request->validate()) {

            return $this->response->array(['data' => $this->repository->create($request->all())]);
        }

        return $this->response->errorBadRequest();
    }

    public function destroy(int $id, Request $request)
    {
        $requestData = $request->all();
        $requestData['report_id'] = $id;
        $request = new DeleteReportRequest($requestData);

        $report = $this->repository->find($id);
        $reportType = $report->report_type;

        if ($request->validate() && $this->repository->delete($request->all())) {

            return $this->response->array(['data' => $this->repository->getAll([
                'report_type' => $reportType,
                'user_id' => $request->get('user_id')
            ])]);
        }

        return $this->response->errorBadRequest();
    }

    public function getFilteredLeads(Request $request)
    {
        $request = new GetFilteredLeadsReportRequest($request->all());

        if ($request->validate()) {
        
            return $this->response->array(['data' => $this->repository->filterLeads($request->all())]);
        }

        return $this->response->errorBadRequest();
    }

    public function getFilteredInventories(Request $request)
    {
        $request = new GetFilteredInventoriesReportRequest($request->all());

        if ($request->validate()) {
        
            return $this->response->array(['data' => $this->repository->filterInventories($request->all())]);
        }

        return $this->response->errorBadRequest();

    }
}