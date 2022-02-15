<?php


namespace App\Http\Controllers\v1\Parts;


use App\Http\Controllers\RestfulControllerV2;
use App\Repositories\Parts\AuditLogRepository;
use App\Transformers\Parts\AuditLogDateCsvTransformer;
use App\Repositories\Parts\AuditLogRepositoryInterface;
use App\Transformers\Parts\AuditLogDateTransformer;
use Dingo\Api\Http\Request;
use App\Http\Requests\Parts\GetAuditLogDate;
use Carbon\Carbon;

/**
 * Class AuditLogController
 *
 * Controller for parts audit log
 *
 * @package App\Http\Controllers\v1\Parts
 */
class AuditLogDateController extends RestfulControllerV2
{
    /**
     * @var AuditLogRepository
     */
    private $auditLogRepository;
    /**
     * @var AuditLogTransformer
     */
    private $auditLogTransformer;

    public function __construct(
        AuditLogRepositoryInterface $auditLogRepository,
        AuditLogDateTransformer $auditLogTransformer
    ) {
        $this->auditLogRepository = $auditLogRepository;
        $this->auditLogTransformer = $auditLogTransformer;

        $this->middleware('setDealerIdOnRequest')->only(['index', 'csv']);
    }
    
    /**
     * @OA\Get(
     *     path="parts/audit-logs/date",
     *     description="Retrieves Audit Log in a Given Date",
     *     tags={"PartsAuditLog"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="year",
     *         required=true,
     *         @OA\Schema(type="year")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Returns an audit log",
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
        $request = new GetAuditLogDate($request->all());

        if ($request->validate()) {
            return $this->response->paginator(
                    $this->auditLogRepository->getByYear((int)$request->year, (int)$request->dealer_id)->paginate($request->per_page)->appends($request->all()), 
                    $this->auditLogTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
    
    public function csv(Request $request)
    {
        $request = new GetAuditLogDate($request->all());
        if ($request->validate()) {
            return $this->response->array(
                    $this->auditLogRepository->getByYearCsv((int)$request->year, (int)$request->dealer_id), 
                    new AuditLogDateCsvTransformer);
        }
        
        return $this->response->errorBadRequest();
    }
}
