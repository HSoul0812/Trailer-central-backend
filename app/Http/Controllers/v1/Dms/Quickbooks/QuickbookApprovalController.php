<?php

namespace App\Http\Controllers\v1\Dms\Quickbooks;

use App\Http\Controllers\RestfulController;
use App\Http\Requests\Dms\Quickbooks\DeleteQuickbookApprovalRequest;
use App\Http\Requests\Dms\Quickbooks\UpdateQuickbookApprovalRequest;
use Dingo\Api\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\Repositories\Dms\Quickbooks\QuickbookApprovalRepositoryInterface;
use App\Transformers\Dms\Quickbooks\QuickbookApprovalTransformer;
use App\Http\Requests\Dms\Quickbooks\GetQuickbookApprovalRequest;

/**
 * @author Marcel
 */
class QuickbookApprovalController extends RestfulController
{

    protected $quickbookApprovalRepo;

    protected $transformer;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(QuickbookApprovalRepositoryInterface $quickbookApprovalRepo)
    {
        $this->middleware('setDealerIdOnRequest')->only(['index']);
        $this->quickbookApprovalRepo = $quickbookApprovalRepo;
        $this->transformer = new QuickbookApprovalTransformer();
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
            return $this->response->paginator($this->quickbookApprovalRepo->getAll($request->all()), $this->transformer);
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
    public function delete(int $qbId)
    {
        $request = new DeleteQuickbookApprovalRequest(['id' => $qbId]);

        if ($request->validate()) {
            $params = [
                'id' => $qbId,
                'user' => Auth::user()->getAuthIdentifierName()
            ];
            return $this->quickbookApprovalRepo->delete($params);
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
            return $this->quickbookApprovalRepo->update($request->all());
        }

        return $this->response->errorBadRequest();
    }
}
