<?php

namespace App\Http\Controllers\v1\Dms\Quickbooks;

use App\Http\Controllers\RestfulControllerV2;
use App\Http\Requests\Dms\Quickbooks\DeleteQuickbookApprovalRequest;
use App\Http\Requests\Dms\Quickbooks\UpdateQuickbookApprovalRequest;
use App\Models\CRM\Dms\Quickbooks\QuickbookApproval;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalDeletedRepositoryInterface;
use Dingo\Api\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepositoryInterface;
use App\Transformers\Dms\Quickbooks\QuickbookApprovalTransformer;
use App\Http\Requests\Dms\Quickbooks\GetQuickbookApprovalRequest;

/**
 * @author Marcel
 */
class QuickbookApprovalController extends RestfulControllerV2
{

    protected $quickbookApprovalRepo;

    protected $quickbookApprovalDeletedRepo;

    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @param QuickbookApprovalRepositoryInterface $quickbookApprovalRepo
     * @param QuickbookApprovalDeletedRepositoryInterface $quickbookApprovalDeletedRepo
     */
    public function __construct(
        QuickbookApprovalRepositoryInterface $quickbookApprovalRepo,
        QuickbookApprovalDeletedRepositoryInterface  $quickbookApprovalDeletedRepo
    )
    {
        $this->quickbookApprovalRepo = $quickbookApprovalRepo;
        $this->quickbookApprovalDeletedRepo = $quickbookApprovalDeletedRepo;
        $this->transformer = new QuickbookApprovalTransformer();

        $this->middleware('setDealerIdOnRequest')->only([
            'index', 'destroy', 'update', 'moveStatus'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/dms/quickbooks/quickbook-approvals",
     *     description="Retrieve a list of quickbook approvals",
     *     tags={"Quickbook Approval"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Page Limit",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="dealer_id",
     *         in="query",
     *         description="Dealer ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search_term",
     *         in="query",
     *         description="Search term",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns a list of quickbook approvals",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function index(Request $request)
    {
        $request = new GetQuickbookApprovalRequest($request->all());

        if ($request->validate()) {
            if ($request->get('status') === QuickbookApproval::REMOVED) {
                return $this->response->paginator($this->quickbookApprovalDeletedRepo->getAll($request->all()), $this->transformer);
            } else {
                return $this->response->paginator($this->quickbookApprovalRepo->getAll($request->all()), $this->transformer);
            }
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Delete(
     *     path="/api/dms/quickbooks/quickbook-approvals/{id}",
     *     description="Delete given quickbook approval record with soft delete.",
     *     tags={"Quickbook Approval"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Primary key of quickbook record.",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Remove operation is success.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function destroy(int $qbId, Request $request)
    {
        $request = new DeleteQuickbookApprovalRequest(['id' => $qbId] + $request->all());

        if ($request->validate()) {
            return $this->quickbookApprovalRepo->delete($request->all());
        }

        return $this->response->errorBadRequest();
    }

    /**
     * @OA\Put (
     *     path="/api/dms/quickbooks/quickbook-approvals/{id}/{status}",
     *     description="Updates given quickbook approval with given status.",
     *     tags={"Quickbook Approval"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Primary key of quickbook record.",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="New status for entity.",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Update operation is success.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Error: Bad request.",
     *     ),
     * )
     */
    public function moveStatus(int $qbId, string $status)
    {
        $request = new UpdateQuickbookApprovalRequest(['id' => $qbId, 'status' => $status]);

        if ($request->validate()) {
            return $this->quickbookApprovalDeletedRepo->delete($request->all());
        }

        return $this->response->errorBadRequest();
    }
}
