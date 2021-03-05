<?php

namespace App\Repositories\Parts;

use App\Models\Parts\AuditLog;
use App\Repositories\RepositoryAbstract;
use App\Utilities\JsonApi\WithRequestQueryable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class AuditLogRepository extends RepositoryAbstract implements AuditLogRepositoryInterface
{
    use WithRequestQueryable;
    
    /**     
     * @var App\Models\Parts\AuditLog 
     */
    protected $model;
    
    public function __construct(AuditLog $auditLog)
    {
        $this->model = $auditLog;
    }

    public function getAll($params)
    {
        return $this->query()->get();
    }
    
    public function getByDate(Carbon $date, int $dealerId) : Collection
    {
        return $this->model
                    ->join('parts_v1', 'parts_v1.id', '=', 'parts_audit_log.part_id')
                    ->whereBetween('parts_audit_log.created_at', [$date->format('Y-m-d').' 00:00:00', $date->format('Y-m-d').' 23:59:59'])
                    ->where('parts_v1.dealer_id', $dealerId)
                    ->get();
    }
    
    public function getByYear(int $year, int $dealerId) : Builder
    {
        return $this->model
                    ->select("parts_audit_log.*", "parts_v1.id", "parts_v1.dealer_id", "parts_v1.dealer_cost", "parts_v1.price", "parts_v1.vendor_id")
                    ->join('parts_v1', 'parts_v1.id', '=', 'parts_audit_log.part_id')
                    ->whereBetween('parts_audit_log.created_at', ["$year-01-01 00:00:00", "$year-12-31 23:59:59"])
                    ->where('parts_audit_log.balance', '>', 0)
                    ->where('parts_v1.dealer_id', $dealerId)
                    ->orderBy('parts_audit_log.created_at', 'DESC');
    }

    /**
     * @param $params
     * @return AuditLog
     */
    public function create($params)
    {
        $auditLog = new AuditLog($params);
        $auditLog->save();

        return $auditLog;
    }

    protected function baseQuery()
    {
        return AuditLog::query();
    }

}
