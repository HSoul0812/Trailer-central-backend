<?php


namespace App\Http\Controllers\v1\Parts;


use App\Http\Controllers\RestfulControllerV2;
use App\Models\Parts\AuditLog;
use App\Repositories\Parts\AuditLogRepository;
use App\Repositories\Parts\AuditLogRepositoryInterface;
use App\Transformers\Parts\AuditLogTransformer;
use Dingo\Api\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Serializer\ArraySerializer;

/**
 * Class AuditLogController
 *
 * Controller for parts audit log
 *
 * @package App\Http\Controllers\v1\Parts
 */
class AuditLogController extends RestfulControllerV2
{
    /**
     * @var AuditLogRepository
     */
    private $auditLogRepository;
    /**
     * @var AuditLogTransformer
     */
    private $auditLogTransformer;
    /**
     * @var Manager
     */
    private $fractal;

    public function __construct(
        AuditLogRepositoryInterface $auditLogRepository,
        AuditLogTransformer $auditLogTransformer,
        Manager $fractal

    ) {
        $this->auditLogRepository = $auditLogRepository;
        $this->auditLogTransformer = $auditLogTransformer;
        $this->fractal = $fractal;

        $this->middleware('setDealerIdOnRequest')->only(['index']);
    }

    public function index(Request $request)
    {
        $this->fractal->setSerializer(new ArraySerializer());
        $this->fractal->parseIncludes($request->query('with', ''));

        $result = $this->auditLogRepository
            ->withRequest($request) // pass jsonapi request queries onto this queryable repo
            ->getAll([]);

        $data = new Collection($result, $this->auditLogTransformer);
        $data->setPaginator(new IlluminatePaginatorAdapter($this->auditLogRepository->getPaginator()));

        $result = (array)$this->fractal->createData($data)->toArray();
        return $this->response->array($result);
    }
}
