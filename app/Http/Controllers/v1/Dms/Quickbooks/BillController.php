<?php
namespace App\Http\Controllers\v1\Dms\Quickbooks;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Dms\Bill\AddBillItemRequest;
use App\Http\Requests\Dms\Bill\CreateBillRequest;
use App\Http\Requests\Dms\Bill\GetBillRequest;
use App\Http\Requests\Dms\Bill\UpdateBillRequest;
use App\Repositories\Dms\Quickbooks\BillRepository;
use App\Transformers\Dms\Bill\BillTransformer;
use Dingo\Api\Http\Request;

class BillController extends RestfulControllerV2
{
    /** @var BillRepository */
    private $billRepository;

    /** @var BillTransformer */
    private $billTransformer;

    /**
     * BillController constructor.
     * @param BillRepository $billRepository
     * @param BillTransformer $billTransformer
     */
    public function __construct(BillRepository $billRepository, BillTransformer $billTransformer)
    {
        $this->billRepository = $billRepository;
        $this->billTransformer = $billTransformer;

        $this->middleware('setDealerIdOnRequest');
    }

    public function index(Request $request)
    {
        $request = new GetBillRequest($request->all());

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $bill = $this->billRepository->getAll($request->all(), true);
        return $this->response->paginator($bill, $this->billTransformer);
    }

    public function show($id, Request $request)
    {
        $request = new GetBillRequest($request->all());
        $params['id'] = $id;

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $bill = $this->billRepository->get($request->all());
        return $this->response->item($bill, $this->billTransformer);
    }

    public function create(Request $request)
    {
        $request = new CreateBillRequest($request->all());

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $bill = $this->billRepository->create($request->all());
        return $this->response->item($bill, $this->billTransformer);
    }

    public function update($id, Request $request)
    {
        $requestData = $request->all();
        $requestData['id'] = $id;
        $request = new UpdateBillRequest($requestData);

        if (!$request->validate()) {
            return $this->response->errorBadRequest();
        }

        $bill = $this->billRepository->update($requestData);
        return $this->response->item($bill, $this->billTransformer);
    }
}